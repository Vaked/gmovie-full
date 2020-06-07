<?php

namespace App\Service;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class MovieMapper
{
    private $movies = array();
    private $container;
    private $entityManager;

    public function __construct(
        ContainerBagInterface $container,
        EntityManagerInterface $entityManager
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    private function getDirector(array $apiData)
    {
        $director = 'N/A';
        if (!empty($apiData) && isset($apiData['credits']['crew'])) {
            foreach ($apiData['credits']['crew'] as $crewMember) {
                if (strtolower($crewMember['job']) === strtolower('Director')) {
                    $director = $crewMember['name'];
                }
            }
        }
        return $director;
    }

    private function getCast(array $movieData)
    {
        $cast = 'N/A';
        if (!empty($movieData) && isset($movieData['credits']['cast'])) {
            $topThree = array_slice($movieData['credits']['cast'], 0, 3);
            $cast = implode(',', array_map(function ($cast) {
                return $cast['name'] ?? NULL;
            }, $topThree));
        }
        return $cast;
    }

    private function getGenres(array $apiData)
    {
        $genres = 'N/A';
        if (!empty($apiData) && isset($apiData['genres'])) {
            $genres = implode(',', array_map(function ($genres) {
                return $genres['name'] ?? NULL;
            }, $apiData['genres']));
        }
        return $genres;
    }

    public function mapToEntity(array $apiData)
    {
        $movie = $this->entityManager->getRepository(Movie::class)->findByUuid($apiData['id']);
        if (!$movie) {
            $movie = new Movie();
            $movie->setUuid($apiData['id'] ?? new Exception("No ID found"));
            $movie->setTitle($apiData['title'] ?? 'N/A');
            $movie->setYear($apiData['release_date'] ?? 'N/A');
            $movie->setDirector($this->getDirector($apiData));
            $movie->setGenre($this->getGenres($apiData));
            $movie->setCast($this->getCast($apiData));
            $movie->setRate($apiData['vote_average'] ?? 'N/A');
            $movie->setSourceClass("https://api.themoviedb.org/3/");
            $this->entityManager->persist($movie);
            $this->entityManager->flush();
        }
        $movie->setPosterPath(
            isset($apiData['poster_path']) ?
                $this->container->get('image_source') . $apiData['poster_path']
                : 'N/A'
        );
        $movie->setSynopsis($apiData['overview'] ?? 'N/A');
        array_push($this->movies, $movie);
        return $this;
    }

    public function getMovies($count)
    {
        $movies = $this->movies ?? null;
        if (count($this->movies) >= $count) {
            $movies = array_slice($this->movies, 0, $count);
        }
        $this->movies = [];
        return $movies;
    }

    public function getMovie()
    {
        $movie = null;
        if (!empty($this->movies)) {
            $movie = $this->movies[0];
            $this->movies = [];
        }
        return $movie;
    }
}
