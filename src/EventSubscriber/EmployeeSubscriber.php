<?php
// api/src/EventSubscriber/BookMailSubscriber.php
namespace App\EventSubscriber;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Book;
use App\Entity\Company\Employee;
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
    public function __construct(EmailService $mailer)
    {
        $this->mailer = $mailer;
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['sendMail', EventPriorities::POST_WRITE],
        ];
    }
    public function sendMail(ViewEvent $event): void
    {
        $book = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        $this->mailer ->sendEmail("Test","szymonkadelski@gmail.com","test ".json_encode($book));
    }
}