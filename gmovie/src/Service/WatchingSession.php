<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WatchingSession
{

    private $cookies;
    private $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->cookies = $_COOKIE;
    }

    public function setSession(): void
    {
        $this->session->set('watching', true);
    }

    private function stopSession()
    {
        $this->session->set('watching', false);
    }

    public function isWatching(): bool
    {
        $response = false;
        if (!isset($this->cookies['watching'])) {
            $this->stopSession();
        }
        if (isset($this->cookies['watching']) && $this->session->get('watching') === true) {
            $response = true;
        }

        return $response;
    }
}
