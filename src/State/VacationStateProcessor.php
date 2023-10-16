<?php

namespace App\State;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationLimits;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\Settings\NotificationRepository;
use App\Repository\VacationRepository;
use App\Repository\VacationStatusRepository;
use App\Service\EmailService;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
        private IriConverterInterface $iriConverter
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

                    }

                    if($this->notificationRepository->getNotificationsSettings()?->getNotificateDepartmentModOnCreatedVacation())
                    {
                        $this->emailService->sendNotificationToModofDepartment($data->getEmployee());
                    }
                }
            } elseif ($operation instanceof Put) {
                if ($data->getType()->getId() != 1 && $data->getType()->getId() != 11) {
                    $this->checkVacationLimits($data);
                }

                if($data->getStatus() != $context["previous_data"]->getStatus())
                {
                    if($data->getStatus()->getName() == "Potwierdzony") {

                        $data->setAcceptedAt(new DateTime());

                        $user = $this->security->getUser();

                        if($user instanceof  User){
                            $data->setAcceptedBy($user);
                        }

                        if($this->notificationRepository -> getNotificationsSettings() ?->getNotificateAdminOnAcceptVacation()) {
                            $this->emailService -> sendNotificationEmailToAllAdmin($data->getEmployee());
                        }

                        if ($this->notificationRepository -> getNotificationsSettings() ?->getNotificateReplacementUser() && !empty($data->getReplacement())) {
                            $this->emailService -> sendReplacementEmployeeNotification($data->getEmployee(),$data->getReplacement());
                        }

                        if ($this->notificationRepository -> getNotificationsSettings() ?-> getNotificateUserOnVacationRequestAccept()) {
                            $this->emailService -> sendNotificationToOwnerOnAccept($data->getEmployee());
                        }
                    }
                }

                if ($context["previous_data"]->getStatus()->getName() == "Potwierdzony" && $data->getStatus()->getName() == "Anulowany")
                {
                    $date = date('Y-m-d');
                    if($this->security->getUser()->getId() == $data->getEmployee()->getUser()->getId() ??"" && $date <= $data->getDateFrom()) {
                        $user = $this->security->getUser();

                        $data->setAnnulledAt(new DateTime());

                        if ($user instanceof User) {
                            $data->setAnnulledBy($user);
                        }
                    }

                    if($this->security->isGranted("ROLE_ADMIN")&& $date <= $data->getDateTo()) {
                        $user = $this->iriConverter->getResourceFromIri(
                            'api/users/' . $this->security->getUser()->getId()
                        );

                        $data->setAnnulledAt(new DateTime());

                        if ($user instanceof User) {
                            $data->setAnnulledBy($user);
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
