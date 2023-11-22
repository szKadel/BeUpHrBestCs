<?php

namespace App\Controller\Vacation;

use App\Entity\Vacation\VacationLimits;
use App\Repository\EmployeeVacationLimitRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class LimitsVacationController
{
    public function __construct(
        private EmployeeVacationLimitRepository $employeeVacationLimitRepository
    )
    {
    }

    public function getVacationLimit($vacation):VacationLimits
    {
        $limit = $this->employeeVacationLimitRepository->findLimitByTypes(
            $vacation->getEmployee(),
            $vacation->getType()
        );

        if (empty($limit[0]) || !($limit[0] instanceof VacationLimits)) {
            throw new BadRequestException('Ten Urlop nie został przypisany dla tego użytkownika.');
        }

        return $limit[0];
    }


}