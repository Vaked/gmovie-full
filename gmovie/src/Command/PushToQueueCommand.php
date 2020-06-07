<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use App\Repository\UserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use App\Repository\TemplateRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Exception;

class PushToQueueCommand extends Command
{
    protected static $defaultName = 'app:push-queue';
    private $userRepository;
    private $templateRepository;
    private $container;

    public function __construct(
        UserRepository $userRepository,
        TemplateRepository $templateRepository,
        ContainerBagInterface $container
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->templateRepository = $templateRepository;
        $this->container = $container;
    }

    private function isToday(?string $time)
    {
        $result = false;
        $today = date('d-m-Y', strtotime('today'));
        if ($time === $today) {
            $result = true;
        }
        return $result;
    }

    private function getTemplateDate(string $time)
    {
        switch ($time) {
            case 'ED':
                return date('d-m-Y', strtotime("today"));
            case 'EW':
                return date('d-m-Y', strtotime("this week"));
            case 'ED':
                return date('d-m-Y', strtotime("today"));
            case 'BOM':
                return date('01-m-Y');
            case 'E2W':
                return date('d-m-Y', strtotime('this month second monday'));
            case 'E3W':
                return date('d-m-Y', strtotime('this month third monday'));
            case 'EOM':
                return date('d-m-Y', strtotime('last day of this month'));
            default:
                return null;
        }
    }

    private function getActiveTemplates()
    {
        $activeTemplates = array();
        $templates = $this->templateRepository->findAll();
        $activeTemplates = array_filter($templates, function ($template) {
            if ($this->isToday($this->getTemplateDate($template->getRule()))) {
                return $template;
            }
        });
        return $activeTemplates;
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
        $activeTemplates = $this->getActiveTemplates();
        $users = $this->userRepository->findAll();
        if (empty($activeTemplates)) {
            exit;
        }
        $message = new AMQPMessage();
        $message->set('content_type', 'text/plain');
        $message->set('delivery_mode', AMQPMessage::DELIVERY_MODE_PERSISTENT);
        foreach ($users as $user) {
            foreach ($activeTemplates as $activeTemplate) {
                $messageBody = json_encode([
                    "id" => $user->getId(),
                    "templateId" => $activeTemplate->getId()
                ]);
                $message->setBody($messageBody);
                $channel->basic_publish($message, $exchange);
            }
        }
        $channel->close();
        $connection->close();
        return 0;
    }
}
