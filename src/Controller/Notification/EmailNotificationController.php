<?php

namespace App\Controller\Notification;

use App\Entity\Settings\Notification;
use App\Entity\Vacation\Vacation;
use App\Repository\Settings\NotificationRepository;
use App\Service\EmailService;

class EmailNotificationController
{
    private Notification | null $notification;

    public function __construct(
        private NotificationRepository $notificationRepository,
        private EmailService $emailService
    )
    {
    }

    private function setNotificationsSettings()
    {
        $this->notification = $this->notificationRepository->getNotificationsSettings();
    }

    public function OnVacationAdd(Vacation $vacation):void
    {
        $this->setNotificationsSettings();

        if ($this->notification == null || $vacation->getType()->getName() == "Plan urlopowy")
        {
            return;
        }

        if($this->notification ->isNotificateDepartmentModOnCreatedVacation())
        {
            $this->emailService->sendNotificationToModofDepartment($vacation);
        }

        if($this->notification ->isNotificateUserOnVacationRequestAccept())
        {
            $this->emailService->sendNotificationToOwnerOnCreate($vacation);
        }
    }

    public function OnVacationUpdate():void
    {

    }
}