<?php

namespace App\Controller\Entity;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\User;
use App\Entity\Vacation\Vacation;
use App\Entity\Vacation\VacationFile;
use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\VacationTypes;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\VacationRepository;
use App\Repository\VacationTypesRepository;
use App\Service\Vacation\CounterVacationDays;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VacationController extends AbstractController
{

    public function __construct(private CounterVacationDays $counterVacationDays,
    private IriConverterInterface $iriConverter
        )
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
            $spendDays = $this->counterVacationDays->countHolidaysForEmployee($user->getEmployee());

            $limit = $vacationLimit instanceof VacationLimits ? $vacationLimit->getDaysLimit() + $vacationLimit->getUnusedDaysFromPreviousYear() : 0;
            $leftVacationDays = $limit - $spendDays;
        }


        return new JsonResponse([
            'spendVacationsDays' => $spendDays ?? 0,
            'vacationDaysLeft' => $leftVacationDays ?? 0,
            'vacationDaysLimit' => $limit ?? 0
        ]);
    }

    #[Route('/api/calendar/vacations', methods: ['GET'])]
    public function getAllVacationAndSortThem(
        VacationRepository $vacationRepository,
        Request $request
    )
    {

        $resultDb = $vacationRepository->findAllVacationForCompany(
            $request->get("dateFrom") ?? throw new BadRequestException("dateFrom is required"),
            $request-> get("dateTo") ?? throw new BadRequestException("dateTo is required"),
            $request-> get("department") ?? null
        );

        foreach ($resultDb as $vacation) {
            $result[] = [
                'vacation' => json_encode($this->iriConverter->getResourceFromIri('api/vacations/'.$vacation->getId())),
                'employee' => json_encode($this->iriConverter->getResourceFromIri('api/employees/'.$vacation->getEmployee()->getId())),
                'dateFrom' => $vacation->getDateFrom()->format('Y-m-d'),
                'dateTo' => $vacation->getDateTo()->format('Y-m-d')
            ];

        }

        return new JsonResponse(json_encode($result) ?? []);
    }


    #[Route('/api/vacation/{vacationId}/file/', methods: ['GET'])]
    public function downloadFile(string $vacationId, VacationRepository $vacationRepository): Response
    {
        $vacation = $vacationRepository ->find($vacationId);

        if(!$vacation instanceof Vacation){
            throw new BadRequestException("Nie znaleziono obiektu vacation");
        }

        if($vacation->getFile()?->getId() == null){
            throw new BadRequestException("Nie znaleziono obiektu obiektu VacationFile");
        }

        $file = $vacation->getFile()->getFilePath();
        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/files/vacations/';

        $filePath = $publicDirectory . $file;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Plik nie istnieje.');
        }

        $response = new BinaryFileResponse($filePath);

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file
        ));

        return $response;
    }

}