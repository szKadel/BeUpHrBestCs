<?php

namespace App\Controller\Presist;


use App\Entity\Company\Employee;
use Doctrine\ORM\EntityManagerInterface;

class EmployeePresist
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }


    public function add(Employee $employee)
    {
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }
}