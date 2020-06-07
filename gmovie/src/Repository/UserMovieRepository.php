<?php

namespace App\Repository;

use App\Entity\UserMovie;
use Doctrine\ORM\Query;
use App\Entity\User;
use App\Service\ApiService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserMovie|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMovie|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMovie[]    findAll()
 * @method UserMovie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMovieRepository extends ServiceEntityRepository
{
    protected $apiService;
    public function __construct(ManagerRegistry $registry, ApiService $apiService)
    {
        parent::__construct($registry, UserMovie::class);
        $this->apiService = $apiService;
    }

    public function getWatchedMoviesCount($userid)
    {
        return $this->createQueryBuilder('um')
            ->select('count(um.id)')
            ->andWhere('um.user = :userId')
            ->setParameter('userId', $userid)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUnwatchedMovieCount($userid)
    {
        return $this->createQueryBuilder('um')
            ->select('count(um.id)')
            ->andWhere('um.user = :userId')
            ->andWhere('um.isWatched = 0')
            ->setParameter('userId', $userid)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUserMovies(User $user, bool $isWatched = false)
    {
        $movies = array_map(
            function ($userMovies) {
                return $userMovies->getMovie();
            },
            $this->findBy(
                array(
                    'user' => $user,
                    'isWatched' => $isWatched
                )
            )
        );
        foreach ($movies as $movie) {
            $apiMovie = $this->apiService->getMovie($movie->getUuid());
            $movie->setSynopsis($apiMovie->getSynopsis());
            $movie->setPosterPath($apiMovie->getPosterPath());
        }
        return $movies;
    }
}
