<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use App\Repository\UserMovieRepository;
use App\Entity\UserMovie;
use App\Service\MovieMapper;
use App\Entity\Movie;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\User;

class Recommendations
{
    private $userMovieRepository;
    private $apiService;
    private $user;
    private $container;

    public function __construct(
        UserMovieRepository $userMovieRepository,
        ApiService $apiService,
        Security $security,
        ParameterBagInterface $parameterBag
    ) {
        $this->apiService = $apiService;
        $this->userMovieRepository = $userMovieRepository;
        $this->user = $security->getUser();
        $this->container = $parameterBag;
    }

    public function getRecommendations(): array
    {
        $bucket = array();
        $unwatchedBucketDifference = UserMovie::MAX_BUCKET_MOVIES
            - $this->userMovieRepository->getUnwatchedMovieCount($this->user);
        $userMovies = $this->userMovieRepository->getUserMovies($this->user, true);
        $randomKeys = array_rand($userMovies, 5);
        foreach ($randomKeys as $randomKey) {
            $apiRecommendations = $this->apiService->getRecommendations($userMovies[$randomKey]->getUuid(), 10);
            $bucket[] = $this->buildMovieArray($apiRecommendations);
        }
        $bucket = array_merge([], ...$bucket);
        return array_slice($bucket, 0, $unwatchedBucketDifference);
    }

    private function buildMovieArray(array $apiRecommendations): array
    {
        $listOfNewMovies = array();
        foreach ($apiRecommendations as $recommendation) {
            if (
                !$this->existsInUserMovie($recommendation)
                && $this->isLessThanBucketLimit()
                && $recommendation->getRate() > $this->container->get('min_rating')
            ) {
                $listOfNewMovies[] = $recommendation;
            }
        }
        return $listOfNewMovies;
    }

    private function isLessThanBucketLimit(): bool
    {
        return $this->userMovieRepository->getUnwatchedMovieCount($this->user) <= UserMovie::MAX_BUCKET_MOVIES;
    }

    private function existsInUserMovie(Movie $movie): bool
    {
        $result = true;
        $movies = $this->userMovieRepository->findBy(
            array(
                'movie' => $movie
            )
        );
        if (empty($movies)) {
            $result = false;
        }
        return $result;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }
}
