<?php

namespace App\Controller\Presist;

use App\Entity\Company\Employee;
use App\Entity\Vacation\VacationLimits;
use Doctrine\ORM\EntityManagerInterface;

class VacationLimitPresist
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }


    public function add(VacationLimits $employee)
    {
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }
}