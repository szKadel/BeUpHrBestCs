<?php

namespace App\Service;

use App\Entity\Company\Employee;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{

    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository)
    {

    }

    public function sendEmail(string $subject, string $to, string $body): void
    {
        $email = (new Email())
            ->from('beuphr@beupsoft.pl')
            ->to($to)
            ->subject($subject)
            ->html($body);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {

        }
    }

    public function sendNotificationEmailToAllAdmin(Employee $employee): void
    {
        $admins = $this->userRepository->getAdmins();
        foreach ($admins as $admin) {
            $this->sendEmail(
                "Bestcs Hr - powiadomienie",
                $admin->getEmail(),
                "Wniosek użytkownika " . $employee->getName(
                ) . " " . $employee->getSurname(
                ) . " został zaakceptowany."
            );
        }
    }

    public function sendReplacementEmployeeNotification(Employee $employee, Employee $replacementUser):void
    {
        if (!empty($replacementUser->getEmail())) {
            $this->sendEmail(
                "Bestcs Hr - powiadomienie",
                $replacementUser->getEmail(),
                "Zostałeś przypisany jako zastępstwo za użytkownika " . $employee->getName() . " " . $employee->getSurname() ??""
            );
        }
    }

    public function sendNotificationToModofDepartment(Employee $employee):void
    {
        $mods = $this->userRepository->getModerators($employee->getDepartment());
        foreach ($mods as $mod){
            $this->sendEmail(
                "Bestcs Hr - powiadomienie",
                $mod->getEmail(),
                "Użytkownik ".$employee->getName()." ".$employee->getSurname()." utworzył wniosek urlopowy, który oczekuje na Twoją akceptację."
            );
        }
    }

    public function sendNotificationToOwnerOnAccept(Employee $employee):void
    {
            $this->sendEmail(
                "Bestcs Hr - powiadomienie",
                $employee->getUser()->getEmail(),
                "Użytkownik ".$employee->getName()." ".$employee->getSurname()." utworzył wniosek urlopowy, który oczekuje na Twoją akceptację."
            );
    }
}