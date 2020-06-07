<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\UserMovie;
use App\Repository\MovieRepository;
use App\Repository\UserMovieRepository;
use App\Service\MovieMapper;
use Doctrine\ORM\EntityManagerInterface;

/**
 *
 * @IsGranted("ROLE_USER")
 */
class GettingStartedController extends AbstractController
{

    /**
     * @Route("/getstarted", name="get_started")
     */
    public function index(UserMovieRepository $userMovieRepository)
    {
        return $this->render('getStarted/index.html.twig', [
            'moviesLeft' => UserMovie::MIN_REQUIRED_MOVIES - $userMovieRepository->getWatchedMoviesCount($this->getUser())
        ]);
    }

    /**
     * @Route("/search/{name}", name="search")
     */
    public function searchMovie(string $name, ApiService $apiService)
    {
        $result = $apiService->getMovieByName($name);
        return new JsonResponse(
            array(
                'status' => 'Ok',
                'data' => $this->renderView('getStarted/movies.html.twig', ['movies' => $result])
            ),
            200
        );
    }

    /**
     * @Route("/add/{uuid}", name="add_movie")
     */
    public function addMovie(
        $uuid,
        ApiService $apiService,
        EntityManagerInterface $entityManager,
        UserMovieRepository $userMovieRepository,
        MovieRepository $movieRepository
    ) {
        $movie = $movieRepository->findByUuid($uuid);
        if (empty($movie)) {
            $movie = $apiService->getMovie($uuid);
        } else {
            $movie = $movieRepository->findByUuid($uuid);
        }
        $data = [
            'status' => false,
            'moviesLeft' => UserMovie::MIN_REQUIRED_MOVIES - $userMovieRepository->getWatchedMoviesCount($this->getUser()) - 1
        ];
        $statusCode = 400;
        if (empty($userMovieRepository->findBy(
            array(
                'movie' => $movie,
                'user' => $this->getUser()
            )
        ))) {
            $userMovie = new UserMovie();
            $userMovie->setMovie($movie);
            $userMovie->setUser($this->getUser());
            $userMovie->setIsWatched(true);
            $userMovie->setStatus(UserMovie::SEEN);
            $entityManager->persist($userMovie);
            $entityManager->flush();
            $data['status'] = true;
            $statusCode = 200;
        }

        return new JsonResponse(
            $data,
            $statusCode
        );
    }
}
