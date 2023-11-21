<?php

namespace Functional\Api\Vacation;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Vacation\VacationFile;
use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\VacationTypesFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class VacationFileObjectTest extends ApiTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testCreateAMediaObject(): void
    {
        $file = new UploadedFile('files/test.txt', 'test.txt');
        $client = self::createClient();

        $client->request('POST', '/api/vacation_files', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'parameters' => [
                    'title' => 'My file uploaded',
                ],
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);

        $department2 = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne();
        $employee3 = EmployeeFactory::createOne(['department'=>$department2]);

        $user2 = UserFactory::createOne(['employee' => $employee3, 'roles'=>['ROLE_MOD']]);

        $user = UserFactory::createOne(['employee' => $employee, 'roles'=>['ROLE_MOD']]);

        $vacationType = VacationTypesFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->get('/api/vacation_files',[])->dd();


        $this->assertResponseIsSuccessful();
    }
}