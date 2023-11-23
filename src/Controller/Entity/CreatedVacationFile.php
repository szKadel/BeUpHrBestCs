<?php

namespace App\Controller\Entity;

use App\Entity\Vacation\VacationFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class CreatedVacationFile extends AbstractController
{
    public function __invoke(Request $request): VacationFile
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $mediaObject = new VacationFile();
        $mediaObject->file = $uploadedFile;
        $mediaObject->setFileName($uploadedFile->getClientOriginalExtension());


        return $mediaObject;
    }
}