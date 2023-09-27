<?php

namespace App\Controller\Presist;

use App\Entity\Company\Department;
use Doctrine\ORM\EntityManagerInterface;

class DepartmentPresist
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }


    public function add(Department $department)
    {
        $this->entityManager->persist($department);
        $this->entityManager->flush();
    }
}