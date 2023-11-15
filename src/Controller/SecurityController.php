<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Controller\Authorisation\ApiTokenController;
use App\Entity\ApiToken;
use App\Entity\User;
use App\Entity\Vacation\VacationLimits;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\VacationTypesRepository;
use App\Service\Vacation\CounterVacationDays;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Driver\Exception\AuthenticationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private CounterVacationDays $counterVacationDays)
    {

    }


    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(IriConverterInterface $iriConverter,#[CurrentUser] User $user = null, ApiTokenController $apiTokenController) :Response
    {
        if($user === null ){
            throw new UnauthorizedHttpException("");
        }

        if($user -> getApiTokens()->get(0) === null)
        {
            $token = new ApiToken();
            $token ->setOwnedBy($user);
            $apiTokenController -> add($token);
        }elseif(!$user -> getApiTokens()->get(0)->isValid()){

            if(!empty($user -> getApiTokens()->get(0))) {
                $apiTokenController->delete($user->getApiTokens()->get(0));
            }

            $token = new ApiToken();
            $token ->setOwnedBy($user);
            $apiTokenController -> add($token);
        }

        $token = new ApiToken();
        $token ->setOwnedBy($user);
        $apiTokenController -> add($token);

        $response = [
            'token' => $token->getToken(),
            'user'=>$user->getId(),
            'expiredAt' => new ($token->getExpiresAt())
        ];

        return new JsonResponse($response,200);
    }

    #[Route('/logout',name: 'app_logout')]
    public function logout():void
    {

    }

    #[Route('/api/getCurrentUser/', name: 'app_check_user', methods: ['GET'])]
    public function getCurrentUser(IriConverterInterface $iriConverter, VacationTypesRepository $typesRepository, EmployeeVacationLimitRepository $employeeVacationLimitRepository, #[CurrentUser] User $user):Response
    {
        if(empty($user->getId()) || $user === null)
        {
            return new JsonResponse([
                'error' => 'User dont found'
            ]);
        }

        if(!empty($user->getEmployee())) {

            $vacationType = $typesRepository->findBy(["name"=>"Urlop Wypoczynkowy"])[0] ?? 0;
            $vacationLimit = $employeeVacationLimitRepository->findBy(["Employee"=>$user->getEmployee(),"vacationType"=>$vacationType])[0]?? 0;
            $spendDays = $vacationLimit instanceof VacationLimits ? $this->counterVacationDays->getVacationDaysSpend($user->getEmployee(),$vacationType) : 0;
            $limit = $vacationLimit instanceof VacationLimits ? $vacationLimit->getDaysLimit() : 0;
            $leftVacationDays = $limit - $spendDays;

            $employee = [
                    '@id' => $iriConverter->getIriFromResource($user->getEmployee()) ?? "",
                    'id' => $user->getEmployee()?->getId(),
                    'name' => $user->getEmployee()->getName()??"",
                    'surname' => $user->getEmployee()->getSurname() ?? "",
                    'department' => [
                        '@id' => $iriConverter->getIriFromResource($user->getEmployee()->getDepartment()) ?? "",
                        'id' => $user->getEmployee()->getDepartment()->getId() ?? "",
                        'name' => $user->getEmployee()->getDepartment()->getName() ?? ""
                    ],
                    'spendVacationsDays' => $spendDays ?? 0,
                    'vacationDaysLeft' => $leftVacationDays ?? 0,
                    'vacationDaysLimit' => $limit
                ] ?? null;

            $extendedAccess = $user->getEmployee()->getEmployeeExtendedAccesses();
            if(!empty($extendedAccess))  {
                foreach ($extendedAccess as $access) {
                    $employee["employeeExtendedAccesses"][] = [
                        'department' => [
                            '@id' => $iriConverter->getIriFromResource($access->getDepartment()) ?? "",
                            'id' => $access->getDepartment()->getId() ?? "",
                            'name' => $access->getDepartment()->getName() ?? ""
                        ]
                    ];
                }
            }
        }

        return new JsonResponse([
            'id' =>$user->getId(),
            'email'=>$user->getEmail(),
            'roles'=>$user->getRoles(),
            'userName' => $user->getUsername(),
            'employee' => $employee ?? null
        ]);
    }

    #[Route('/api/user/changePassword', methods: ['POST'])]
    public function updatePassword(#[CurrentUser] User $user, UserPasswordHasherInterface $userPasswordHasher, Request $request)
    {
        $requestData = json_decode($request->getContent(), true);

        if (empty($requestData['oldPassword'])) {
            throw new BadRequestException("oldPassword is required");
        }

        if (empty($requestData['newPassword'])) {
            throw new BadRequestException("newPassword is required");
        }

        if ($requestData['oldPassword'] == $requestData['newPassword']) {
            throw new BadRequestException("newPassword and newPassword can't be the same");
        }

        $oldPassword = $requestData['oldPassword'];
        $newPassword = $requestData['newPassword'];

        if ($userPasswordHasher->isPasswordValid($user, $oldPassword)) {

            $hashedNewPassword = $userPasswordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedNewPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(["message" => "Twoje hasło zostało zmienione."]);
        } else {
            throw new BadRequestException("Aktualne hasło jest niepoprawne.");
        }
    }
}