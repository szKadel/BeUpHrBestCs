<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Employee;
use App\Entity\Vacation\Vacation;

class EmployeeStateProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if($data instanceof Employee) {
            if ($operation instanceof Post) {
                $this->addEmployee();
            }
        }
    }

    private function addEmployee()
    {

    }

    private function addVacationLimits()
    {

    }
}
