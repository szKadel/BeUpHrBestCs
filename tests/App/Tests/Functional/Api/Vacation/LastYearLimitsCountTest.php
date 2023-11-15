<?php

namespace  App\Tests\Functional\Api\Vacation;

use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\Vacation\VacationFactory;
use App\Factory\Vacation\VacationLimitsFactory;
use App\Factory\Vacation\VacationStatusFactory;
use App\Factory\VacationTypesFactory;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class LastYearLimitsCountTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testYearCount()
    {
        VacationStatusFactory::createOne(['name'=>'Oczekujący']);
        VacationStatusFactory::createOne(['name'=>'Zaplanowany']);

        $vacationType2 =    VacationTypesFactory::createOne(['name'=>"Na żądanie"]);



        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['employee' => $employee, 'roles'=>['ROLE_ADMIN']]);

        $vacationType =    VacationTypesFactory::createOne(['name'=>"Urlop Wypoczynkowy"]);
        $this->browser()
            ->actingAs($user)
            ->post('/api/vacation_limits',['json'=>[
                'Employee'=>"/api/employees/".$employee->getId(),
                'vacationType'=> "/api/vacation_types/".$vacationType->getId(),
                'daysLimit'=> 4
            ]
            ])->assertStatus(201);

        $this->browser()
            ->actingAs($user)
            ->post('/api/vacation_limits',['json'=>[
                'Employee'=>"/api/employees/".$employee->getId(),
                'vacationType'=> "/api/vacation_types/".$vacationType2->getId(),
                'daysLimit'=> 26
            ]
            ])->assertStatus(201);

        //VacationLimitsFactory::createOne(["Employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>26]);
        //VacationLimitsFactory::createOne(["Employee"=>$employee,'vacationType'=>$vacationType2, 'daysLimit'=>4]);



        VacationFactory::createOne(['employee' => $employee, 'type'=>$vacationType, 'dateFrom' => new DateTime('2023-11-01'), 'dateTo'=>new DateTime('2023-11-05')]);
        VacationFactory::createOne(['employee' => $employee, 'type'=>$vacationType, 'dateFrom' => new DateTime('2023-11-08'), 'dateTo'=>new DateTime('2023-11-15')]);

        $this->browser()
            ->actingAs($user)
            ->get("/Vacations/YearSummation")
            ->assertStatus(200);

        $this->browser()
            ->actingAs($user)
            ->get("/api/vacation_limits")
            ->dump();
    }

}