<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Employee;
use App\Entity\Vacation\Vacation;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

class EmployeeStateProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if($data instanceof Employee) {
            if ($operation instanceof Post) {

            }elseif($operation instanceof Put){

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
