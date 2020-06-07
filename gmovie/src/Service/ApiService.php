<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Service\MovieMapper;
use Exception;

class ApiService
{
    protected $client;
    protected $movieClass;
    protected $container;
    protected $mapper;
    const PAGE_COUNT = 20;

    public function __construct(ContainerBagInterface $container, MovieMapper $mapper)
    {
        $this->client = HttpClient::create();
        $this->container = $container;
        $this->mapper = $mapper;
        $this->movieClass = $this->container->get('source_class');
    }

    //THIS REQUEST RETURNS A SINGLE MOVIE DETAIS BASED ON PASSED IN movieId
    public function getMovie($movieId)
    {
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}movie/{$movieId}"
                . "?api_key={$this->container->get('api_key')}"
                . "&append_to_response=credits"
        );

        return $this->mapper
            ->mapToEntity($response->toArray())
            ->getMovie();
    }

    //RETURNS API DATA IN ARRAY FORMAT
    public function getMovieArray($movieId)
    {
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}movie/{$movieId}"
                . "?api_key={$this->container->get('api_key')}"
                . "&append_to_response=credits"
        );

        return $response->toArray();
    }

    private function validateGenres(array $genres)
    {
        $ids = array_map(function ($genre) {
            return $genre['id'];
        }, $this->getGenres());
        $names = array_map(function ($genre) {
            return $genre['name'];
        }, $this->getGenres());

        foreach ($genres as $genre) {
            if (in_array($genre, $ids) || in_array($genre, $names)) {
                return true;
            } else {
                throw new Exception('Invalid genre provided');
            }
        }
    }

    //THIS REQUEST RETURNS TOP 20 MOVIES BASED ON RATING OVER 6.9 AND PASSED IN GENRES
    public function getMoviesByGenre(array $genres, $count = self::PAGE_COUNT)
    {
        $this->validateGenres($genres);
        $genres = implode(',', $genres);
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}discover/movie"
                . "?api_key={$this->container->get('api_key')}"
                . "&language=en-US"
                . "&sort_by=popularity.desc"
                . "&include_adult=false"
                . "&include_video=false"
                . "&page=1"
                . "&vote_average.gte=6.9"
                . "&with_genres={$genres}"
        );
        $response = $response->toArray()['results'];
        foreach ($response as $movie) {
            $this->mapper->mapToEntity(
                $this->getMovieArray($movie['id'])
            );
        }
        return $this->mapper->getMovies($count);
    }


    public function getTrendingMovies()
    {
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}trending/movie/week"
                . "?api_key={$this->container->get('api_key')}"
                . "&include_adult=false"
        );
        $response = $response->toArray()['results'];
        foreach ($response as $movie) {
            $this->mapper->mapToEntity(
                $this->getMovieArray($movie['id'])
            );
        }
        return $this->mapper->getMovies(10);
    }

    // This method returns top 20 similar movies to the one passed in. 
    public function getRecommendations($movieId, $count)
    {
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}movie/{$movieId}/recommendations"
                . "?api_key={$this->container->get('api_key')}"
                . "&language=en-US&page=1"
        );
        $response = $response->toArray()['results'];
        foreach ($response as $movie) {
            $this->mapper->mapToEntity(
                $this->getMovieArray($movie['id'])
            );
        }
        return $this->mapper
            ->getMovies($count);
    }

    //RETURNS GENRES
    public function getGenres()
    {
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}genre/movie/list"
                . "?api_key={$this->container->get('api_key')}"
                . "&language=en-US"
        );
        return $response->toArray()['genres'];
    }

    public function getMovieByName(string $name)
    {
        $response = $this->client->request(
            'GET',
            "{$this->movieClass}search/movie"
                . "?api_key={$this->container->get('api_key')}"
                . "&language=en-US&page=1"
                . "&query={$name}"
        );
        $response = $response->toArray()['results'];
        foreach ($response as $movie) {
            $this->mapper->mapToEntity(
                $this->getMovieArray($movie['id'])
            );
        }
        return $this->mapper
            ->getMovies(4);
    }
}
