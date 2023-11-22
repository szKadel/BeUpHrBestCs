<?php
// api/src/EventSubscriber/BookMailSubscriber.php
namespace App\EventSubscriber;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Controller\Presist\VacationLimitPresist;
use App\Entity\Book;
use App\Entity\Company\Employee;
use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\VacationTypes;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\VacationTypesRepository;
use App\Service\EmailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
final class EmployeeSubscriber implements EventSubscriberInterface
{
    private $mailer;
    public function __construct(
        private VacationTypesRepository $vacationTypesRepository,
        private VacationLimitPresist $vacationLimitPresist
    )
    {

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['addNewEmployee', EventPriorities::POST_WRITE,EventPriorities::POST_WRITE],
        ];
    }

    public function addNewEmployee(ViewEvent $event): void
    {
        $employee = $event->getRequest()->attributes->get("data");
        $method = $event->getRequest()->getMethod();

        if (!$employee instanceof Employee || Request::METHOD_POST !== $method) {
            return;
        }

        $types = $this->vacationTypesRepository->findAll();

        foreach ($types as $type ){
            $vacationLimit = new VacationLimits();
            $vacationLimit->setEmployee($employee);
            $vacationLimit->setVacationType($type);
            $vacationLimit->setDaysLimit($this->setDaysLimitDependsOnVacationType($type));
            $this->vacationLimitPresist->add($vacationLimit);
        }
    }

    public function setDaysLimitDependsOnVacationType(VacationTypes $type)
    {
        switch ($type->getId())
        {
            case 2:
                return 26;
            case 5:
                return 4;
            case 8:
            case 9:
            case 15:
                return 2;
            default:
                return 0;
        }
    }
}