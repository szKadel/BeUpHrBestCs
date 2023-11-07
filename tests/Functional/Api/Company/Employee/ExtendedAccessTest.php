<?php

namespace Functional\Api\Company\Employee;

use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\Vacation\VacationFactory;
use App\Factory\Vacation\VacationLimitsFactory;
use App\Factory\Vacation\VacationStatusFactory;
use App\Factory\VacationTypesFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ExtendedAccessTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testPutExtendedAccess()
    {
        VacationStatusFactory::createOne(['name'=>'Oczekujący']);
        VacationStatusFactory::createOne(['name'=>'Zaplanowany']);
        $vacationStatus = VacationStatusFactory::createOne(['name'=>'Zaakceptowany']);

        $department = DepartmentFactory::createOne();
        $department2 = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne(['department'=>$department2]);
        $employee2 = EmployeeFactory::createOne(['department'=>$department]);
        EmployeeFactory::createOne(['department'=>$department2]);

        $employeeMod = EmployeeFactory::createOne(['department'=>$department, 'employeeExtendedAccesses'=>[]]);

        $mod = UserFactory::createOne(['employee' => $employeeMod, 'roles'=>['ROLE_MOD']]);

        $user = UserFactory::createOne(['employee' => $employee, 'roles'=>['ROLE_ADMIN']]);
        $user2 = UserFactory::createOne(['employee' => $employee2, 'roles'=>['ROLE_USER']]);

        VacationTypesFactory::createOne();
        $vacationType = VacationTypesFactory::createOne(['name'=>'Inny']);
        $vacationType2 = VacationTypesFactory::createOne(['name'=>'Urlop']);

        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>500]);
        VacationLimitsFactory::createOne(["employee"=>$employeeMod,'vacationType'=>$vacationType, 'daysLimit'=>20]);
        VacationLimitsFactory::createOne(["employee"=>$employee2,'vacationType'=>$vacationType, 'daysLimit'=>20]);

        VacationFactory::createMany(5,["employee"=>$employee,'type'=>$vacationType]);
        VacationFactory::createMany(5,["employee"=>$employeeMod,'type'=>$vacationType]);
        VacationFactory::createMany(5,["employee"=>$employee2,'type'=>$vacationType]);

        $this->browser()
            ->actingAs($user)
            ->post('api/employee/department/',[
                'json'=>[
                    'iri'=>'api/employees/'.$employeeMod->getId(),
                    'departments'=> ['api/departments/'.$department->getId()],
                ]
            ])
            ->assertStatus(200);

        $this->browser()
            ->actingAs($user)
            ->put('api/employee/department/',[
                'json'=>[
                    'iri'=>'api/employees/'.$employeeMod->getId(),
                    'departments'=> ['api/departments/'.$department2->getId()],
                ]
            ])
            ->assertStatus(200);

        // Wnioski jako mod
        $this->browser()
            ->actingAs($mod)
            ->get('api/vacations',[])
            ->assertStatus(200);
    }
}