<?php


namespace App\Controller\View;

use App\Repository\VacationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    public function __construct(
        private VacationRepository $vacationRepository
    )
    {
    }

    #[Route('/api/vacations/week/current')]
    #[IsGranted('ROLE_USER')]
    public function getEmployeeOnVacation()
    {
        $now = new \DateTime();
        $weekStartDate = clone $now;

        $weekStartDate->modify('this week');
        $weekEndDate = $weekStartDate->modify('+4 days');

        $dbResult = $this->vacationRepository->findEmployeeOnVacation($weekStartDate, $weekEndDate);

        foreach ($dbResult as $vacation){
            $result[] = [
                'vacation_id'=>$vacation ->getId(),
                'employee_id'=>$vacation ->getEmployee()->getId(),
                'employee' => $vacation -> getEmployee()->getSurname()." ". $vacation -> getEmployee()->getName()??"" ,
                'dataFrom' => $vacation -> getDateFrom(),
                'dataTo' => $vacation -> getDateTo(),
                'replacement_name' => $vacation ?-> getReplacement() ?-> getName() ??"",
                'replacement_surname' => $vacation ?-> getReplacement() ?-> getSurname()??"",
            ];
        }

        return new JsonResponse($result ?? "");
    }
}