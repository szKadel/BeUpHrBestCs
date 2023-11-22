<?php

namespace Functional\Api\Vacation;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\Vacation\VacationFactory;
use App\Factory\Vacation\VacationLimitsFactory;
use App\Factory\Vacation\VacationStatusFactory;
use App\Factory\VacationTypesFactory;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class VacationPostTest extends ApiTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testAddNewUnActiveEmployeeVacation()
    {
        VacationStatusFactory::createOne(['name'=>'Oczekujący']);
        VacationStatusFactory::createOne(['name'=>'Zaplanowany']);
        $vacationStatus = VacationStatusFactory::createOne(['name'=>'Zaakceptowany']);

        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne(['unActive'=>true]);
        $employee2 = EmployeeFactory::createOne();
        $employee3 = EmployeeFactory::createOne();

        $vacationType = VacationTypesFactory::createOne();
        $vacationType2 = VacationTypesFactory::createOne();

        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>500]);
        VacationLimitsFactory::createOne(["employee"=>$employee2,'vacationType'=>$vacationType2, 'daysLimit'=>500]);

        $user = UserFactory::createOne(['employee'=>$employee2,'password'=>'pass','roles'=>['ROLE_ADMIN']]);
        $user2 = UserFactory::createOne(['employee'=>$employee,'password'=>'pass','roles'=>['ROLE_ADMIN']]);

        $this->browser()
            ->actingAs($user)
            ->post('/api/vacations',[
                'json'=>[
                    'employee'=>'api/employees/'.$employee->getId(),
                    'type'=> 'api/vacation_types/'.$vacationType->getId(),
                    'dateFrom'=> '2023-09-15',
                    'dateTo'=>'2023-09-21'
                ]
            ])
            ->assertStatus(400);
    }


}