<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class Mailer
{
    protected $mailer;
    protected $container;

    public function __construct(
        MailerInterface $mailer,
        ContainerBagInterface $container
    ) {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function notifyUser($user, $renderedTemplate, $subject = 'Welcome')
    {
        $email = (new TemplatedEmail())
            ->from($this->container->get('contact_email'))
            ->to($user->getEmail())
            ->subject($subject)
            ->html($renderedTemplate);
        $this->mailer->send($email);
    }
}