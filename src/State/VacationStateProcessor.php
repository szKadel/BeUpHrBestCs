<?php

namespace App\State;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Controller\Vacation\StatusVacationController;
use App\Controller\Vacation\VacationRequestController;
use App\Entity\User;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationLimits;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\Settings\NotificationRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Repository\VacationStatusRepository;
use App\Service\EmailService;
use App\Service\Vacation\CounterVacationDays;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class VacationStateProcessor implements ProcessorInterface
{
    public function __construct(
        private VacationRequestController $vacationRequestController,
        private ProcessorInterface $innerProcessor,
        private Security $security,
        private EmployeeVacationLimitRepository $employeeVacationLimitRepository,
        private EmailService $emailService,
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
        private CounterVacationDays $counterVacationDays,
        private VacationStatusRepository $vacationStatusRepository
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
                $this->vacationRequestController->onVacationRequestPost($data);

            } elseif ($operation instanceof Put) {
                if($data->getEmployee()->getUnActive()){
                    throw new BadRequestException("Wniosek tego pracownika jest dezaktywowany.");
                }

                if ($data->getType()->getId() != 1 || $data->getType()->getId() != 11) {
                    $this->checkVacationLimits($data);
                }

                if($data->getStatus() != $context["previous_data"]->getStatus())
                {
                    if($data->getStatus()->getName() == "Anulowany") {
                        $user = $this->security->getUser();

                        $data->setAnnulledAt(new \DateTimeImmutable());

                        $data->setAnnulledBy($this->userRepository->find($user->getId()));
                    }

                    if($data->getStatus()->getName() == "Potwierdzony") {

                        $data->setAcceptedAt(new \DateTimeImmutable());

                        $user = $this->security->getUser();

                        if(!$this -> security -> isGranted('ROLE_ADMIN')){
                            if($user -> getEmployee() ->getId() == $data->getEmployee()->getId()) {
                                throw new BadRequestException('Brak Uprawnień do akceptacji własnego wniosku');
                            }
                        }

                        $data->setAcceptedBy($this->userRepository->find($user->getId()));

                        if ($this->notificationRepository -> getNotificationsSettings() ?->isNotificateReplacementUser() && !empty($data->getReplacement())) {
                            $this->emailService -> sendReplacementEmployeeNotification($data);
                        }
                    }

                    if($data->getStatus()->getName() == "Odrzucony") {

                        if ($this->notificationRepository->getNotificationsSettings(
                        )?->isNotificateAdminOnAcceptVacation()) {
                            $this->emailService->sendNotificationEmailToAllAdmin($data);
                        }

                        if ($this->notificationRepository -> getNotificationsSettings() ?-> isNotificateUserOnVacationRequestAccept()) {
                            $this->emailService -> sendNotificationToOwnerOnChangeStatus($data);
                        }

                        if($this -> security -> isGranted('ROLE_KADR')){
                            throw new BadRequestException('Brak Uprawnień');
                        }
                    }
                    if($data->getStatus()->getName() == "Oczekujacy") {
                        if ($this->notificationRepository->getNotificationsSettings(
                        )?->isNotificateAdminOnAcceptVacation()) {
                            $this->emailService->sendNotificationEmailToAllAdmin($data);
                        }
                    }
                }

                if ($context["previous_data"]->getStatus()->getName() == "Potwierdzony" && $data->getStatus()->getName() == "Anulowany")
                {
                    if($this -> security -> isGranted('ROLE_KADR')){
                        throw new BadRequestException('Brak Uprawnień');
                    }

                    $date = date('Y-m-d');

                    if($this->security->getUser()->getId() == $data->getEmployee()->getUser()?->getId()) {
                        $user = $this->security->getUser();

                        $data->setAnnulledAt(new \DateTimeImmutable());

                        $data->setAnnulledBy($data->getEmployee()->getUser());
                    }

                    if($this->security->isGranted("ROLE_ADMIN")&& $date <= $data->getDateTo()) {
                        $user = $this->security->getUser();

                        $data->setAnnulledAt(new \DateTimeImmutable());

                        $data->setAnnulledBy($this->userRepository->find($user->getId()));

                    }
                }

                if ($data->getType() != $context["previous_data"]->getType())
                {
                    if($context["previous_data"]->getType()->getName() == "Plan urlopowy") {
                        $data->setStatus($this->vacationStatusRepository->findByName("Oczekujący"));
                    }
                }

                if ($data->getType() != $context["previous_data"]->getType())
                {
                    if($context["previous_data"]->getType()->getName() == "Oczekujący" && $data->getType()->getName() == 'Plan urlopowy') {
                        $data->setStatus($this->vacationStatusRepository->findByName("Zaplanowany"));
                    }
                }
            }

            $data->setSpendVacationDays();

            if($data->getSpendVacationDays() == 0){
                throw new BadRequestException('Wniosek nie może być wystawiony na 0 dni.');
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

        $vacationUsedInDays = $this->counterVacationDays->countVacationSpendDays(
            $vacation->getEmployee(),
            $vacation->getType()
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
                throw new BadRequestException('Uwaga! Nie możesz zaakceptować tego wniosku. Limit na ten rok dla tego użytkownika został wykorzystany.');
            }
        }
    }
}
