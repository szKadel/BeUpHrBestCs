<?php

namespace App\Repository;

use App\Entity\Company\Department;
use App\Entity\Company\Employee;
use App\Entity\EmployeeExtendedAccesses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeeExtendedAccesses>
 *
 * @method EmployeeExtendedAccesses|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmployeeExtendedAccesses|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmployeeExtendedAccesses[]    findAll()
 * @method EmployeeExtendedAccesses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeExtendedAccessesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, EmployeeExtendedAccesses::class);
    }

    public function addNew(Employee $employee, Department $department)
    {
        $newData = new EmployeeExtendedAccesses();
        $newData->setEmployee($employee);
        $newData->setDepartment($department);

        $this->entityManager->persist($newData);
        $this->entityManager->flush();
    }

    public function removeExtendedAccessById(int $id): void
    {
        $extendedAccess = $this->find($id);

        if ($extendedAccess !== null) {
            $this->_em->remove($extendedAccess);
            $this->_em->flush();
        }
    }


//    /**
//     * @return EmployeeExtendedAccesses[] Returns an array of EmployeeExtendedAccesses objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmployeeExtendedAccesses
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
