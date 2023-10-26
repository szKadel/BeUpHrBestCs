<?php

namespace App\Controller\Vacation;

use App\Entity\Vacation\VacationStatus;
use App\Repository\VacationStatusRepository;
use ContainerRW4SEIY\getVacationStatusRepositoryService;

class StatusVacationController
{
    public function __construct(
        private VacationStatusRepository $vacationStatusRepository
    )
    {
    }

    public function getVacationStatus(string $statusName)
    {
        return $this->vacationStatusRepository->findByName($statusName);
    }

    public function setStatusForCreatedVacation($vacation) : VacationStatus
    {
           return $vacation->getType()->getId() == 1 ?
                $this->getVacationStatus('Zaplanowany')
                : $this->getVacationStatus('Oczekujący');

    }
}