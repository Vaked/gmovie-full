<?php

namespace App\Repository;

use App\Entity\AchievementBadge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AchievementBadge|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchievementBadge|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchievementBadge[]    findAll()
 * @method AchievementBadge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchievementBadge::class);
    }
}
