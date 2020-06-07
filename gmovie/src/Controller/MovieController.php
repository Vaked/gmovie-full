<?php

namespace App\Controller;

use App\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MovieRepository;
use App\Repository\UserMovieRepository;
use App\Service\AchievementChecker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use App\Annotation\EntryComplete;
use App\Entity\UserMovie;
use App\Service\WatchingSession;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * @IsGranted("ROLE_USER")
 * @EntryComplete
 */

class MovieController extends AbstractController
{
    /**
     * @Route("/movie/{movieId}", name="movie")
     */
    public function show($movieId, MovieRepository $movieRepository)
    {
        $movie = $movieRepository
            ->getMovie($movieId);
        return $this->render('movie/index.html.twig', [
            'movie' => $movie
        ]);
    }


    /**
     * @Route("/movie/history/{movieId}/{status}", name="movie_history")
     * @Entity("movie", expr="repository.find(movieId)")
     */
    public function movieHistory(
        Movie $movie,
        $status,
        UserMovieRepository $userMovieRepository,
        EntityManagerInterface $entityManager,
        AchievementChecker $achievementChecker,
        WatchingSession $watchingSession
    ) {
        $response = new JsonResponse();
        $data = ['status' => 'Error', 'message' => 'Error', 'achievements' => null];
        $statusCode = 400;
        $achievements = null;
        if (isset($movie) && isset($status) && (!$watchingSession->isWatching() || $status == UserMovie::SEEN)) {
            if ($status === (string) UserMovie::LIKE || $status === (string) UserMovie::DISLIKE) {
                $response->headers->setCookie(new Cookie('watching', true, time() + 7200));
                $watchingSession->setSession();
            }
            $featuredMovie = $userMovieRepository->findOneBy(
                array(
                    'movie' => $movie,
                    'user'  => $this->getUser()
                )
            );
            $featuredMovie->setIsWatched(UserMovie::LIKE);
            $featuredMovie->setStatus($status);
            $entityManager->persist($featuredMovie);
            $entityManager->flush();

            //checks if user is eligible for an achievement
            if ($achievementChecker->isEligibleForAchievement()) {
                $achievements = array_map(function ($achievements) {
                    return $achievements->getAchievement()->getName();
                }, $achievementChecker->getAchievementsWon());
            };
            $data['status'] = 'Ok';
            $data['message'] = 'Success';
            $data['achievements'] = $achievements;
            $statusCode = 200;
        }
        $response->setData($data);
        $response->setStatusCode($statusCode);
        return $response;
    }
}
