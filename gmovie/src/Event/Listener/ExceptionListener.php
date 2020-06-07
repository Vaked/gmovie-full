<?php

namespace App\Event\Listener;

use App\Exceptions\EntryNotCompleteException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof EntryNotCompleteException)) {
            return;
        }

        $response = new RedirectResponse('/getstarted');

        $event->setResponse($response);
    }
}
