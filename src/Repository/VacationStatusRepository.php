<?php

namespace App\Repository;

use App\Entity\Vacation\VacationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VacationStatus>
 *
 * @method VacationStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method VacationStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method VacationStatus[]    findAll()
 * @method VacationStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacationStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VacationStatus::class);
    }

    public function findOneBySomeField($value): ?VacationStatus
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByName($value): ?VacationStatus
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
