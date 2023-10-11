<?php

namespace App\Tests\Functional\Api\Vacation\Limits;

use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\Vacation\VacationLimitsFactory;
use App\Factory\VacationTypesFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class LimitsRequestResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testLimitAdd()
    {

        $department2 = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne();
        $employee3 = EmployeeFactory::createOne(['department'=>$department2]);

        $user2 = UserFactory::createOne(['employee' => $employee3, 'roles'=>['ROLE_MOD']]);

        $user = UserFactory::createOne(['employee' => $employee, 'roles'=>['ROLE_MOD']]);

        $vacationType = VacationTypesFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->post('/api/vacation_limits',['json'=>[
                'Employee'=>"/api/employees/".$employee->getId(),
                'vacationType'=> "/api/vacation_types/".$vacationType->getId(),
                'daysLimit'=> 100
            ]
            ])->assertStatus(201);

        $this->browser()
            ->actingAs($user2)
            ->post('/api/vacation_limits',['json'=>[
                'Employee'=>"/api/employees/".$employee3->getId(),
                'vacationType'=> "/api/vacation_types/".$vacationType->getId(),
                'daysLimit'=> 100
            ]
            ])->assertStatus(201);

        $this->browser()
            ->actingAs($user2)
            ->post('/api/vacation_limits',['json'=>[
                'Employee'=>"/api/employees/".$employee3->getId(),
                'vacationType'=> "/api/vacation_types/".$vacationType->getId(),
                'daysLimit'=> 100
            ]
            ])->assertStatus(400);

        $this->browser()
            ->actingAs($user)
            ->post('/api/vacation_limits',['json'=>[
                'Employee'=>"/api/employees/".$employee->getId(),
                'vacationType'=> "/api/vacation_types/".$vacationType->getId(),
                'daysLimit'=> 100
            ]
            ])->assertStatus(400);
    }

}