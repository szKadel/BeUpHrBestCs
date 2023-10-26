<?php

namespace App\Controller\Vacation;

use App\Controller\Notification\EmailNotificationController;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationLimits;
use App\Repository\Settings\NotificationRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Service\EmailService;
use App\Service\Vacation\CounterVacationDays;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class VacationRequestController
{

    private Vacation $vacation;

    public function __construct(
        private VacationRepository $vacationRepository,
        private LimitsVacationController $limitsVacationController,
        private StatusVacationController $statusVacationController,
        private CounterVacationDays $counterVacationDays,
        private EmailNotificationController $emailNotificationController,
        private Security $security,
        private UserRepository $userRepository
    )
    {
    }

    private function setVacation(Vacation $vacation)
    {
        $this->vacation = $vacation;
    }

    private function setPreaviusVacation(Vacation $vacation)
    {
        $this->vacation = $vacation;
    }

    public function onVacationRequestPost(Vacation $vacation)
    {
        $this -> setVacation($vacation);
        $this -> checkDateAvailability();
        $this -> checkVacationStatus();
        $this -> checkVacationDaysLimit();
        $this -> checkReplacement();
        $this -> vacation -> setCreatedBy($this->userRepository->find($this->security->getUser()->getId()));
        $this -> vacation -> setCreatedAt(new \DateTime());
        $this -> emailNotificationController    ->  OnVacationAdd($vacation);
    }

    public function onVacationUpdate(Vacation $vacation, Vacation $previousVacation)
    {
        $this   ->setVacation($vacation);
        $this   ->setPreaviusVacation($previousVacation);
        $this   ->checkVacationDaysLimit();
        $this   ->checkReplacement();


    }

    public function checkReplacement(): void
    {
        if(!empty($this->vacation->getReplacement())) {

            if($this->vacation->getEmployee()->getId() == $this->vacation->getReplacement()->getId() ){
                throw new BadRequestException("Osoba tworząca urlop nie może być jednocześnie osobą zastępującą.", 400);
            }

            $this->vacationRepository->findExistingVacationForUserInDateRange(
                $this->vacation->getReplacement(),
                $this->vacation->getDateFrom(),
                $this->vacation->getDateTo()
            );
        }
    }

    private function checkDateAvailability():void
    {
        $this->vacationRepository->findExistingVacationForUserInDateRange($this->vacation->getEmployee(),$this->vacation->getDateFrom(),$this->vacation->getDateTo());
    }

    private function checkVacationStatus():void
    {
        $this->vacation->setStatus($this->statusVacationController->setStatusForCreatedVacation($this->vacation));
    }

    private function checkVacationDaysLimit(): void
    {
        if ($this->vacation->getType()->getId() == 1 || $this->vacation->getType()->getId() == 11) {
            return;
        }

        $limitDays = $this->getVacationLimits()->getDaysLimit();

        $spendDays = $this->counterVacationDays->getVacationDaysSpend($this->vacation->getEmployee(),$this->vacation->getType(),$this->vacation->getStatus());

        if ($limitDays == 0) {
            return;
        }

        if ($limitDays < $spendDays + $this->vacation->getSpendVacationDays()) {
            throw new BadRequestException('Drogi Pracowniku! Wniosek nie może zostać utworzony z powodu przekroczenia limitu dostępnych dni wolnych.');
        }
    }

    private function getVacationLimits():VacationLimits
    {
        return $this->limitsVacationController->getVacationLimit($this->vacation);
    }


}