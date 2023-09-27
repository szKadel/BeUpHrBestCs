<?php

namespace App\Repository;

use App\Entity\Company\Employee;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationStatus;
use App\Entity\Vacation\VacationTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @extends ServiceEntityRepository<\App\Entity\Vacation\Vacation>
 *
 * @method Vacation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vacation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vacation[]    findAll()
 * @method Vacation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,private VacationStatusRepository $vacationStatusRepository)
    {
        parent::__construct($registry, Vacation::class);
    }


    public function findExistingVacationForUserInDateRange(Employee $employee, \DateTimeInterface $startDate, \DateTimeInterface $endDate):void
    {
        $statusAccepted = $this->vacationStatusRepository->findByName("Potwierdzony");
        $statusWaiting = $this->vacationStatusRepository->findByName("Oczekujący");

        $result = $this->createQueryBuilder('v')
            ->andWhere('v.employee = :employee')
            ->andWhere('(v.status = :status or v.status = :status2)')
            ->andWhere('(:dateFrom BETWEEN v.dateFrom AND v.dateTo OR :dateTo BETWEEN v.dateFrom AND v.dateTo OR v.dateFrom BETWEEN :dateFrom AND :dateFrom OR v.dateFrom BETWEEN :dateFrom AND :dateTo OR :dateFrom = v.dateTo OR v.dateTo = :dateFrom OR v.dateFrom = :dateTo)')
            ->setParameter('status', $statusAccepted)
            ->setParameter('status2', $statusWaiting)
            ->setParameter('employee', $employee)
            ->setParameter('dateFrom', $startDate->format('Y-m-d'))
            ->setParameter('dateTo', $endDate->format('Y-m-d'))
            ->getQuery()
            ->getResult();

         if(!empty($result))
         {
             throw new BadRequestException("Wniosek dla ".$employee->getName()." ".$employee->getSurname()." w tym terminie został już złożony");
         }

    }

    public function findVacationUsedByUser(Employee $employee, VacationStatus $vacationStatus, VacationTypes $vacationTypes):int
    {
        $result = $this->createQueryBuilder('v')
            ->andWhere('v.employee = :employee')
            ->andWhere('v.status = :status')
            ->andWhere('v.type = :types')
            ->setParameter('employee', $employee)
            ->setParameter('status', $vacationStatus)
            ->setParameter('types', $vacationTypes)
            ->getQuery()
            ->getResult();
        $days = 0;

        if(!empty($result)) {
            foreach ($result as $element) {
                $days += $element->getSpendVacationDays();
            }
        }

        return $days;
    }
//    /**
//     * @return Vacation[] Returns an array of Vacation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vacation
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
