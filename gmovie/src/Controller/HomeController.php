<?php

namespace App\Controller;

use App\Annotation\EntryComplete;
use App\Entity\UserMovie;
use App\Repository\UserMovieRepository;
use App\Service\Recommendations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @EntryComplete
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(UserMovieRepository $userMovieRepository)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $movies = $userMovieRepository
            ->getUserMovies($this->getUser());
        if (count($movies) === 0) {
            $this->redirectToRoute('fill_bucket');
        }
        return $this->render('home/index.html.twig', [
            'movies' => $movies
        ]);
    }

    /**
     * @Route("/fill_bucket", name="fill_bucket")
     */
    public function fillBucket(
        UserMovieRepository $userMovieRepository,
        Recommendations $recommendations,
        EntityManagerInterface $em
    ) {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $recommendations = $recommendations->getRecommendations();
        foreach ($recommendations as $recommendedMovie) {
            $userMovie = new UserMovie();
            $userMovie->setUserMovie($recommendedMovie, $this->getUser(), false, UserMovie::NOT_SEEN);
            $em->persist($userMovie);
        }
        $em->flush();
        $movies = $userMovieRepository
            ->getUserMovies($this->getUser());
        return $this->redirectToRoute('home', ['movies' => $movies]);
    }
}
