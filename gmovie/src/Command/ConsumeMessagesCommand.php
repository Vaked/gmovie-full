<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use App\Repository\UserRepository;
use App\Repository\TemplateRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Exception;
use App\Service\TPLNotifier;
use Throwable;

class ConsumeMessagesCommand extends Command
{
    protected static $defaultName = 'app:consume';
    private $userRepository;
    private $templateRepository;
    private $container;
    private $notifier;

    public function __construct(
        UserRepository $userRepository,
        TemplateRepository $templateRepository,
        ContainerBagInterface $container,
        TPLNotifier $notifier
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->templateRepository = $templateRepository;
        $this->container = $container;
        $this->notifier = $notifier;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $this->container->get('queue_host');
        $port = $this->container->get('queue_port');
        $rabbitmqUser = $this->container->get('queue_rabbitmqUser');
        $pass = $this->container->get('queue_pass');

        $exchange = $this->container->get('queue_exchange');
        $queue = $this->container->get('queue_queuedev');

        $connection = new AMQPStreamConnection($host, $port, $rabbitmqUser, $pass);
        if (!$connection) {
            throw new Exception('Failed RabbitMQ connection');
        }

        $channel = $connection->channel();

        $channel->queue_declare($queue, false, true, false, false);
        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
        $channel->queue_bind($queue, $exchange);

        $messageLimit = $this->container->get('message_limit');

        for ($i = 0; $i < $messageLimit; $i++) {
            try {
                $message = $channel->basic_get($queue, true);
                $data = json_decode($message->body);

                $user = $this->userRepository->find($data->id);
                $template = $this->templateRepository->find($data->templateId);
                $executionMethod = $template->getExecutionFunction();

                call_user_func([$this->notifier, $executionMethod], $user);
            } catch (Throwable $e) {
                print ("Error caught: " . $e->getMessage());
                continue;
            }
        }
        $channel->close();
        $connection->close();
        return 0;
    }
}
