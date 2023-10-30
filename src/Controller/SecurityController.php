<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Controller\Authorisation\ApiTokenController;
use App\Entity\ApiToken;
use App\Entity\User;
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
    public function getCurrentUser(IriConverterInterface $iriConverter, #[CurrentUser] User $user ):Response
    {
        if(empty($user->getId()) || $user === null)
        {
            return new JsonResponse([
                'error' => 'User dont found'
            ]);
        }

        if(!empty($user->getEmployee())) {
            $employee = [
                    '@id' => $iriConverter->getIriFromResource($user->getEmployee()) ?? "",
                    'id' => $user->getEmployee()?->getId(),
                    'name' => $user->getEmployee()->getName()??"",
                    'surname' => $user->getEmployee()->getSurname() ?? "",
                    'department' => [
                        '@id' => $iriConverter->getIriFromResource($user->getEmployee()->getDepartment()) ?? "",
                        'id' => $user->getEmployee()->getDepartment()->getId() ?? "",
                        'name' => $user->getEmployee()->getDepartment()->getName() ?? ""
                    ]
                ] ?? null;
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
        if( $request->query->has('oldPassword')){
            $oldPassword = $request->query->get('oldPassword');
        }else{
            throw new BadRequestException("oldPassword is required");
        }

        if( $request->query->has('newPassword')){
            $newPassword = $request->query->get('newPassword');
        }else{
            throw new BadRequestException("newPassword is required");
        }

        if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            $user->setPlainPassword($newPassword);

            if($user->getPlainPassword())
            {
                $user->setPassword($userPasswordHasher->hashPassword($user,$user->getPlainPassword()));
                return true;
            }
        }else{
            throw new AuthenticationException("Aktualne hasło jest niepoprawne.");
        }
    }


}