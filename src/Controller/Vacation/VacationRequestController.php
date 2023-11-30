<?php

namespace App\Controller\Vacation;

use App\Controller\Notification\EmailNotificationController;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationLimits;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Service\Vacation\CounterVacationDays;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class VacationRequestController
{

    private Vacation $vacation;

    public function __construct(
        private readonly VacationRepository $vacationRepository,
        private readonly LimitsVacationController $limitsVacationController,
        private readonly StatusVacationController $statusVacationController,
        private readonly CounterVacationDays $counterVacationDays,
        private readonly EmailNotificationController $emailNotificationController,
        private readonly Security $security,
        private readonly UserRepository $userRepository
    )
    {
    }

    private function setVacation(Vacation $vacation):void
    {
        $this->vacation = $vacation;
    }

    private function setPreaviusVacation(Vacation $vacation):void
    {
        $this->vacation = $vacation;
    }

    public function onVacationRequestPost(Vacation $vacation):void
    {
        if($vacation->getEmployee()->getUnActive()){
            throw new BadRequestException("Wniosek tego pracownika jest dezaktywowany.");
        }

        $this -> setVacation($vacation);
        $this -> checkRights();
        $this -> checkDateAvailability();
        $this -> checkVacationStatus();

        if($vacation->getSpendVacationDays() == 0){
            throw new BadRequestException('Wniosek nie może być wystawiony na 0 dni.');
        }

        $this -> checkVacationDaysLimit();
        $this -> checkReplacement();
        $this -> vacation -> setCreatedBy($this->userRepository->find($this->security->getUser()->getId()));
        $this -> vacation -> setCreatedAt(new DateTime());
        $this -> emailNotificationController    ->  OnVacationAdd($vacation);
    }

    public function checkRights() :void
    {
        if($this->security->isGranted('ROLE_ADMIN')){
            return;
        }

        if($this->vacation->getEmployee()->getId() != $this->security->getUser()->getEmployee()->getId()){
            throw new BadRequestException("Brak uprawnień, aby utworzyć wniosek za innego użytkownika",401);
        }
    }

    public function onVacationUpdate(Vacation $vacation, Vacation $previousVacation):void
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

        $limitDays = $this->getVacationLimits()->getDaysLimit() + $this->getVacationLimits()->getUnusedDaysFromPreviousYear() ?? 0;

        $spendDays = $this->counterVacationDays->countVacationSpendDays($this->vacation->getEmployee(),$this->vacation->getType());

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