<?php

namespace App\Event\Listener;

use App\Annotation\EntryComplete;
use App\Entity\UserMovie;
use App\Repository\UserMovieRepository;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use App\Exceptions\EntryNotCompleteException;

class EntryListener
{
    private $annotationReader;
    private $user;
    private $userMovieRepository;
    private $router;

    public function __construct(
        Reader $annotationReader,
        Security $security,
        UserMovieRepository $userMovieRepository,
        RouterInterface $router
    ) {
        $this->annotationReader = $annotationReader;
        $this->user = $security->getUser();
        $this->userMovieRepository = $userMovieRepository;
        $this->router = $router;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controllers = $event->getController();
        if (!is_array($controllers)) {
            return;
        }
        $this->handleAnnotation($controllers);
    }

    private function handleAnnotation(iterable $controllers): void
    {
        list($controller, $method) = $controllers;

        try {
            $controller = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to read annotation!');
        }

        $this->handleClassAnnotation($controller);
    }

    private function handleClassAnnotation(ReflectionClass $controller)
    {
        $annotation = $this->annotationReader->getClassAnnotation($controller, EntryComplete::class);
        if ($annotation instanceof EntryComplete && $this->user) {
            if ($this->userMovieRepository->getWatchedMoviesCount($this->user) < UserMovie::MIN_REQUIRED_MOVIES) {
                throw new EntryNotCompleteException('GetStarted not complete');
            }
        }
    }
}
