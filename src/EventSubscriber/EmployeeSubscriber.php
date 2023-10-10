<?php
// api/src/EventSubscriber/BookMailSubscriber.php
namespace App\EventSubscriber;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Book;
use App\Entity\Company\Employee;
use App\Service\EmailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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
            KernelEvents::RESPONSE => ['sendMail', EventPriorities::POST_WRITE],
        ];
    }
    public function sendMail(ResponseEvent $event): void
    {
        $book = $event->getRequest();
        $method = $event->getRequest()->getMethod();

        $this->mailer ->sendEmail("Test","szymonkadelski@gmail.com","test ".json_encode($book->getContent()));


    }
}