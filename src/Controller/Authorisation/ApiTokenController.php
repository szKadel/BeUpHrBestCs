<?php

namespace App\Controller\Authorisation;

use App\Entity\ApiToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiTokenController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function delete(ApiToken $token)
    {
        $this->entityManager->remove($token);
        $this->entityManager->flush();
    }


    public function add(ApiToken $token)
    {
        $this->entityManager->persist($token);
        $this->entityManager->flush();
    }
}