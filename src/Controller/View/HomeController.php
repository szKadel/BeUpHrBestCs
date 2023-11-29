<?php


namespace App\Controller\View;

use App\Entity\Vacation\Vacation;
use App\Repository\VacationRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function getEmployeeOnVacation() : JsonResponse
    {
        $today = date('Y-m-d');

        $monday = date('Y-m-d', strtotime('last Monday', strtotime($today)));
        $friday = date('Y-m-d', strtotime('this Friday', strtotime($today)));

        $dbResult = $this->vacationRepository->findEmployeeOnVacation($monday, $friday);

        foreach ($dbResult as $vacation) {
            $result[] = [
                'vacation_id' => $vacation->getId(),
                'employee_id' => $vacation->getEmployee()->getId(),
                'employee_name' => $vacation->getEmployee()->getName() ?? "",
                'employee_surname' => $vacation->getEmployee()->getSurname() ?? "",
                'department' => $vacation->getEmployee()->getDepartment()?->getName() ?? "",
                'dateFrom' => $vacation->getDateFrom()->format('Y-m-d'),
                'dateTo' => $vacation->getDateTo()->format('Y-m-d'),
                'replacement_name' => $vacation?->getReplacement()?->getName() ?? "",
                'replacement_surname' => $vacation?->getReplacement()?->getSurname() ?? "",
            ];

        }

        return new JsonResponse($result ?? []);
    }

    #[Route('/api/calendar/vacations')]
    #[IsGranted('ROLE_USER')]
    public function getAllVacationAndSortThem(
        VacationRepository $vacationRepository,
        Request $request
    ): JsonResponse
    {

        $resultDb = $vacationRepository->findAllVacationForCompany(
            $request->query->get('dateFrom') ?? throw new BadRequestException("dateFrom is required"),
            $request->query->get('dateTo') ?? throw new BadRequestException("dateTo is required"),
            $request->query->get("department_id") ?? null
        );

        foreach ($resultDb as $vacation) {
            if ($vacation instanceof Vacation) {
                $result[] = [
                    'vacation_iri' => '/api/vacations/' . $vacation->getId(),
                    'employee_iri' => '/api/employees/' . $vacation->getEmployee()->getId(),
                    'employee_name' => $vacation->getEmployee()->getName() ?? "",
                    'employee_surname' => $vacation->getEmployee()->getSurname() ?? "",
                    'dateFrom' => $vacation->getDateFrom()->format('Y-m-d'),
                    'dateTo' => $vacation->getDateTo()->format('Y-m-d'),
                    'type_iri' => '/api/vacation_types/' . $vacation?->getType()?->getId() ?? "",
                    'type_name' => $vacation?->getType()?->getName() ?? "",
                    'status_iri' => '/api/vacation_statuses/' . $vacation?->getStatus()?->getId() ?? "",
                    'status_name' => $vacation?->getStatus()?->getName() ?? "",
                ];
            }
        }

        return new JsonResponse($result ?? []);
    }
}