<?php

namespace App\Controller;

use App\Controller\Presist\VacationLimitPresist;
use App\Entity\Vacation\VacationLimits;
use App\Repository\EmployeeRepository;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\VacationTypesRepository;
use App\Service\Vacation\CounterVacationDays;
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

    #[Route('/Vacations/YearSummation', methods: ['GET'])]
    public function annualYearSummary(EmployeeRepository $employeeRepository, CounterVacationDays $counterVacationDays, EmployeeVacationLimitRepository $employeeVacationLimitRepository,VacationLimitPresist $vacationLimitPresist, VacationTypesRepository $typesRepository)
    {
        $employees = $employeeRepository -> findAll();
        $holidayType = $typesRepository->findBy(['name'=>'Urlop Wypoczynkowy'])[0];

        foreach ($employees as $employee)
        {
            $spendVacationsDays = $counterVacationDays ->countHolidaysForEmployee($employee);
            $limit = $employeeVacationLimitRepository->findBy(['Employee' => $employee,'vacationType'=>$holidayType])[0] ?? 0;

            if($limit instanceof VacationLimits){
                if ($limit->getDaysLimit() > $spendVacationsDays) {
                    $limit->setUnusedDaysFromPreviousYear($limit->getDaysLimit() - $spendVacationsDays);
                    $vacationLimitPresist->add($limit);

                    $result[] = ["name" => $employee->getName(), "UnusedDaysFromPreviousYear" => $limit->getDaysLimit() - $spendVacationsDays];
                }
            }
        }

        return new JsonResponse([$result ?? []]);
    }
}