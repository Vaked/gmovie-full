<?php

namespace App\Service;

use App\Entity\UserMovie;
use App\Repository\UserMovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\User;
use App\Repository\TemplateRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class TPLNotifier extends Mailer
{
    private $templateRepository;
    private $templateEngine;
    private $entityManager;
    private $recommendations;
    private $userMovieRepository;

    public function __construct(
        ContainerInterface $serviceContainer,
        TemplateRepository $templateRepository,
        EntityManagerInterface $entityManager,
        Recommendations $recommendations,
        UserMovieRepository $userMovieRepository,
        MailerInterface $mailer,
        ContainerBagInterface $container
    ) {
        parent::__construct($mailer, $container);
        $this->templateRepository = $templateRepository;
        $this->templateEngine = $serviceContainer->get('twig');
        $this->entityManager = $entityManager;
        $this->recommendations = $recommendations;
        $this->userMovieRepository = $userMovieRepository;
    }

    public function tplMovieSuggestion(User $user): void
    {
        $template = $this->templateRepository->findOneBy(array(
            'executionFunction' => 'tplMovieSuggestion'
        ));
        $recommendations =  $this->recommendations->setUser($user)
            ->getRecommendations();
        foreach ($recommendations as $recommendedMovie) {
            $userMovie = new UserMovie();
            $userMovie->setUserMovie($recommendedMovie, $user, false, UserMovie::NOT_SEEN);
            $this->entityManager->persist($userMovie);
        }
        $this->entityManager->flush();
        $this->writeToTemplate($template->getContent());
        $renderedTemplate = $this->templateEngine->render('mailer/notify_template.html.twig', [
            'username' => $user->getUsername(),
            'movies' => $this->userMovieRepository->getUserMovies($user)
        ]);
        $this->notifyUser($user, $renderedTemplate, "Movie sugegstion");
    }

    public function tplTrendingMovies(User $user): void
    {
        if ($user->getReceiveAdvertisement() === true) {
            $template = $this->templateRepository->findOneBy(array(
                'executionFunction' => 'tplMovieSuggestion'
            ));
            $this->writeToTemplate($template->getContent());
            $renderedTemplate = $this->templateEngine->render('mailer/notify_template.html.twig', [
                'username' => $user->getUsername(),
                'movies' => $this->apiService->getTrendingMovies()
            ]);
            $this->notifyUser($user, $renderedTemplate, "Trending movies this week");
        }
    }

    public function tplRemindUser(User $user): void
    {
        $currentDate = date_create(date('Y/m/d H:i:s'));
        $difference = date_diff($user->getDate(), $currentDate);
        $difference = $difference->format('%a');
        if ($difference === '7') {
            $template = $this->templateRepository->findOneBy(array(
                'executionFunction' => 'tplRemindUser'
            ));
            $this->writeToTemplate($template->getContent());
            $renderedTemplate = $this->templateEngine->render('mailer/notify_template.html.twig', [
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'activation_code' => $user->getActivationCode()
            ]);
            $this->notifyUser($user, $renderedTemplate);
        }
    }

    public function tplBucketReminder(User $user): void
    {
        if ($user->getReceiveAdvertisement() === true) {
            $template = $this->templateRepository->findOneBy(array(
                'executionFunction' => 'tplBucketReminder'
            ));
            $this->writeToTemplate($template->getContent());
            $renderedTemplate = $this->templateEngine->render('mailer/notify_template.html.twig', [
                'username' => $user->getUsername(),
                'movies' => $this->userMovieRepository->getUserMovies($user)
            ]);
            $this->notifyUser($user, $renderedTemplate, "Unwatched movies in bucket list");
        }
    }

    private function writeToTemplate(string $content)
    {
        $my_file = __DIR__ . $this->container->get('template_dir');
        $handle = fopen($my_file, 'w') or die('Cannot open file:  ' . $my_file);
        fwrite($handle, $content);
    }
}
