<?php

namespace App\Controller;

use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends AbstractController
{
    #[Route('/',name: 'app_main_mainpage')]
    public function mainPage(): RedirectResponse
    {
        return new RedirectResponse('/view/');
    }

    #[Route('/mail/')]
    public function sendEmail(EmailService $emailService): JsonResponse
    {
        $subject = 'Temat wiadomości';
        $to = 'szymon@beupsoft.pl';
        $body = 'Treść wiadomości.';

        $emailService->sendEmail($subject, $to, $body);

        return new JsonResponse(['result'=>true]);
    }
}