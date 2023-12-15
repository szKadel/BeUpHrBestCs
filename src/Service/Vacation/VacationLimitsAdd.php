<?php

namespace App\Service\Vacation;

use App\Controller\Presist\VacationLimitPresist;
use App\Entity\Company\Employee;
use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\VacationTypes;
use App\Repository\VacationTypesRepository;
use http\Exception;

class VacationLimitsAdd
{
    public function __construct(
        private VacationTypesRepository $vacationTypesRepository,
        private VacationLimitPresist $vacationLimitPresist
    )
    {

    }

    public function addLimitsForNewEmployee(Employee $employee)
    {
        $types = $this->vacationTypesRepository->findAll();

        foreach ($types as $type ){
            $vacationLimit = new VacationLimits();
            $vacationLimit->setEmployee($employee);
            $vacationLimit->setVacationType($type);
            $vacationLimit->setDaysLimit($this->setDaysLimitDependsOnVacationType($type));
            $this->vacationLimitPresist->add($vacationLimit);
        }
    }

    private function setDaysLimitDependsOnVacationType(VacationTypes $type)
    {
        switch ($type->getId())
        {
            case 2:
                return 26; // wypoczynkowy
            case 5:
                return 4; // Na zadanie 4
            case 8:
                return 2; // Opieka nad dziezkiem
            case 9:
                return 2;
            default:
                return 0;
        }
    }
}