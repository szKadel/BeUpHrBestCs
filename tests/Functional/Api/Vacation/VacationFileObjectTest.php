<?php

namespace Functional\Api\Vacation;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\Vacation\VacationFactory;
use App\Factory\Vacation\VacationFileFactory;
use App\Factory\Vacation\VacationLimitsFactory;
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
        if (!file_exists('files/test.txt')) {
            touch('files/test.txt');
        }

        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne();
        $employee2 = EmployeeFactory::createOne();
        $employee3 = EmployeeFactory::createOne();

        $vacationType = VacationTypesFactory::createOne();
        $vacationType2 = VacationTypesFactory::createOne();

        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>500]);
        VacationLimitsFactory::createOne(["employee"=>$employee2,'vacationType'=>$vacationType2, 'daysLimit'=>500]);

        $user = UserFactory::createOne(['employee'=>$employee2,'password'=>'pass','roles'=>['ROLE_ADMIN']]);

        $file = new UploadedFile('files/test.txt', 'test.txt');
        $client = self::createClient();

        $client->request('POST', '/api/vacation_files', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);

        $this->browser()
            ->actingAs($user)
            ->get('/api/vacation_files',[])->dd();


        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne();
        $employee2 = EmployeeFactory::createOne();
        $employee3 = EmployeeFactory::createOne();

        $vacationType = VacationTypesFactory::createOne();
        $vacationType2 = VacationTypesFactory::createOne();

        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>500]);
        VacationLimitsFactory::createOne(["employee"=>$employee2,'vacationType'=>$vacationType2, 'daysLimit'=>500]);

        $user = UserFactory::createOne(['employee'=>$employee2,'password'=>'pass','roles'=>['ROLE_ADMIN']]);
        $user2 = UserFactory::createOne(['employee'=>$employee,'password'=>'pass','roles'=>['ROLE_ADMIN']]);

        $fileObject = VacationFileFactory::createOne();

        VacationFactory::createOne(['employee' => $employee3, 'type'=>$vacationType,'replacement'=>$employee,'file'=>$fileObject]);

        $vacationType = VacationTypesFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->get('/api/vacation_files',[])->dd();


        $this->assertResponseIsSuccessful();
    }

    public function getVacationFile()
    {
        if (!file_exists('files/test.txt')) {
            touch('files/test.txt');
        }

        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne();
        $employee2 = EmployeeFactory::createOne();
        $employee3 = EmployeeFactory::createOne();

        $vacationType = VacationTypesFactory::createOne();
        $vacationType2 = VacationTypesFactory::createOne();

        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>500]);
        VacationLimitsFactory::createOne(["employee"=>$employee2,'vacationType'=>$vacationType2, 'daysLimit'=>500]);

        $user = UserFactory::createOne(['employee'=>$employee2,'password'=>'pass','roles'=>['ROLE_ADMIN']]);

        $file = new UploadedFile('files/test.txt', 'test.txt');
        $client = self::createClient();

        $client->request('POST', '/api/vacation_files', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);


    }
}