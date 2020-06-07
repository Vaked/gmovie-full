<?php

namespace App\Repository;

use App\Entity\ResetCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ResetCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResetCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResetCode[]    findAll()
 * @method ResetCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetCode::class);
    }


}
