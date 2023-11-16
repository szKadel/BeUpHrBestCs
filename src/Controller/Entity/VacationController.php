<?php

namespace App\Controller\Entity;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\User;
use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\VacationTypes;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\VacationTypesRepository;
use App\Service\Vacation\CounterVacationDays;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class VacationController extends AbstractController
{

    public function __construct(private CounterVacationDays $counterVacationDays)
    {
    }


    #[Route('/api/getCurrentUser/vacations', methods: ['GET'])]
    public function getVacationSum(IriConverterInterface $iriConverter, VacationTypesRepository $typesRepository, EmployeeVacationLimitRepository $employeeVacationLimitRepository, #[CurrentUser] User $user):Response
    {
        $vacationType = $typesRepository->findBy(["name"=>"Urlop Wypoczynkowy"])[0] ?? 0;
        if($vacationType instanceof VacationTypes) {
            $vacationLimit = $employeeVacationLimitRepository->findBy(
                    ["Employee" => $user->getEmployee(), "vacationType" => $vacationType]
                )[0] ?? 0;
            $spendDays = $this->counterVacationDays->countVacationSpendDays($user->getEmployee(), $vacationType);
            $limit = $vacationLimit instanceof VacationLimits ? $vacationLimit->getDaysLimit() : 0;
            $leftVacationDays = $limit - $spendDays;
        }else{
            $spendDays = 0;
            $leftVacationDays = 0;
            $limit = 0;
        }

        return new JsonResponse([
            'spendVacationsDays' => $spendDays ?? 0,
            'vacationDaysLeft' => $leftVacationDays ?? 0,
            'vacationDaysLimit' => $limit
        ]);
    }

}