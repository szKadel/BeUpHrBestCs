<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\VacationStatus;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\Settings\NotificationRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Repository\VacationStatusRepository;
use App\Service\EmailService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class VacationStateProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private Security $security,
        private VacationRepository $vacationRepository,
        private VacationStatusRepository $vacationStatusRepository,
        private EmployeeVacationLimitRepository $employeeVacationLimitRepository,
        private EmailService $emailService,
        private NotificationRepository $notificationRepository,
        private UserRepository $userRepository
    )
    {

    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): void
    {
        if($data instanceof Vacation) {
            if($operation instanceof Post) {
                if ($this->security->getUser()) {
                    $this->vacationRepository->findExistingVacationForUserInDateRange(
                        $data->getEmployee(),
                        $data->getDateFrom(),
                        $data->getDateTo()
                    );

                    $this->setVacationStatus($data);

                    if ($data->getType()->getId() != 1 && $data->getType()->getId() != 11) {
                        $this->checkVacationLimits($data);
                    }

                    if($data->getEmployee()->getId() == $data->getReplacement()?->getId() ){
                        throw new BadRequestException("Osoba tworząca urlop nie może być jednocześnie osobą zastępującą.", 400);
                    }

                    if(!empty($data->getReplacement())) {
                        $this->vacationRepository->findExistingVacationForUserInDateRange(
                            $data->getReplacement(),
                            $data->getDateFrom(),
                            $data->getDateTo()
                        );

                        if($this->notificationRepository->getNotificationsSettings()?->getNotificateReplacmentUser())
                        {
                            $email = $data->getReplacement()->getUser()?->getEmail();
                            if($email != null ) {
                                $this->sendNotificationEmail(
                                    "Bestcs Hr - powiadomienie",
                                    $email,
                                    "Zostałeś przypisany jako zastępstwo za użytkownika ".$this->security->getUser()?->getEmployee()?->getName()." ".$this->security->getUser()?->getEmployee()?->getSurname()
                                );
                            }
                        }
                    }

                    if($this->notificationRepository->getNotificationsSettings()?->getNotificateDepartmentModOnCreatedVacation())
                    {
                        $mods = $this->userRepository->getModerators();
                        foreach ($mods as $mod) {
                            $this->sendNotificationEmail(
                                "Bestcs Hr - powiadomienie",
                                $mod->getEmial(),
                                "Użytkownik ".$this->security->getUser()?->getEmployee()?->getName()." ".$this->security->getUser()?->getEmployee()?->getSurname()." utworzył wniosek urlopowy, który oczekuje na Twoją akceptację."
                            );
                        }
                    }

                }
            } elseif ($operation instanceof Put) {
                if ($data->getType()->getId() != 1 && $data->getType()->getId() != 11) {
                    $this->checkVacationLimits($data);
                }

                if($this->notificationRepository->getNotificationsSettings()?->getNotifcateAdminOnAcceptVacation())
                {
                    if($data->getStatus()->getName() == "Potwierdzony") {
                        $admins = $this->userRepository->getAdmins();
                        foreach ($admins as $admin) {
                            $this->sendNotificationEmail(
                                "Bestcs Hr - powiadomienie",
                                $admin->getEmail(),
                                "Wniosek użytkownika " . $this->security->getUser()->getEmployee()->getName(
                                ) . " " . $this->security->getUser()->getEmployee()->getSurname(
                                ) . " został zaakceptowany."
                            );
                        }
                    }
                }
            }
        }

        if($data instanceof VacationLimits)
        {
            if($operation instanceof Post) {
                if ($this->employeeVacationLimitRepository->findTypeForEmployee(
                        $data->getEmployee(),
                        $data->getVacationType()
                    ) !== null) {
                    throw new BadRequestException("Limit został już dodany!", 400);
                }
            }
        }

        $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function sendNotificationEmail($title, $to, $body)
    {
        $this->emailService->sendEmail($title, $to, $body);

        return new JsonResponse(['result'=>true]);
    }

    private function checkVacationLimits(Vacation $vacation)
    {

        $vacationUsedInDays = $this->vacationRepository->findVacationUsedByUser(
            $vacation->getEmployee(),
            $vacation->getStatus(),
            $vacation->getType(),
        );

        $limit = $this->employeeVacationLimitRepository->findLimitByTypes(
            $vacation->getEmployee(),
            $vacation->getType()
        );

        if (empty($limit[0])) {
            throw new BadRequestException('Ten Urlop nie został przypisany dla tego użytkownika.');
        }

        if ($limit[0]->getDaysLimit() != 0) {
            if ($limit[0]->getDaysLimit() < $vacationUsedInDays + $vacation->getSpendVacationDays()) {
                throw new BadRequestException(
                    'Nie wystarczy dni Urlopowych. Pozostało ' . $limit[0]->getDaysLimit(
                    ) - $vacationUsedInDays . ". Wnioskujesz o " . $vacation->getSpendVacationDays()
                );
            }
        }
    }


    private function setVacationStatus(Vacation $vacation)
    {
        $vacation->setStatus(
            $vacation->getType()->getId() == 1 ? $this->vacationStatusRepository->findByName(
                'Zaplanowany'
            ) : $this->vacationStatusRepository->findByName('Oczekujący')
        );
    }
}
