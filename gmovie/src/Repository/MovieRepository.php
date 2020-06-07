<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\UserMovie;
use App\Service\ApiService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    protected $apiService;
    public function __construct(ManagerRegistry $registry, ApiService $apiService)
    {
        parent::__construct($registry, Movie::class);
        $this->apiService = $apiService;
    }

    public function getMovie($movieId)
    {
        $movie = $this->find($movieId);
        $apiMovie = $this->apiService->getMovie($movie->getUuid());
        $movie->setSynopsis($apiMovie->getSynopsis());
        $movie->setPosterPath($apiMovie->getPosterPath());
        return $movie;
    }

    public function findByUuid($uuid)
    {
        return $this->findOneBy(array(
            'uuid' => $uuid
        ));
    }
}