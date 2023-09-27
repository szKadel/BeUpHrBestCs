<?php

namespace App\Security;

use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface
{

    public function __construct(private ApiTokenRepository $apiTokenRepository)
    {

    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $token = $this->apiTokenRepository->findOneBy(['token'=>$accessToken]);

        if(!$token){
            throw new BadCredentialsException("Bad Credentials");
        }

        if(!$token->isValid()){
            throw new CustomUserMessageAccountStatusException('Token expired');
        }

        return new UserBadge($token->getOwnedBy()->getUserIdentifier());
    }
}