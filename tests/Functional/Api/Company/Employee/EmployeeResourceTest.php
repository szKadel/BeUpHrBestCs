<?php

namespace App\Tests\Functional\Api\Company\Employee;

use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use App\Factory\VacationTypesFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmployeeResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    public function testGetCollectionOfEmployees()
    {
        $this->browser()
            ->get('/api/employees')
            ->assertStatus(401);

        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['employee' => $employee]);

        $this->browser()
            ->actingAs($user)
            ->get('/api/departments')
            ->assertStatus(200)
            ->assertJsonMatches('"hydra:totalItems"',1);
    }

    public function testPostToAddEmployee()
    {
        $department = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne(['department'=>$department]);
        $user = UserFactory::createOne(['roles'=>['ROLE_ADMIN'],'employee' => $employee]);

        $this->browser()
            ->actingAs($user)
            ->post('/api/employees',['json'=>[
                'name'=>'test',
                'surname'=>'test',
                'department' => 'api/departments/'.$department->getId()
                ]
            ])
            ->assertStatus(201);
    }

    public function testPutToUpdateEmployee()
    {
        $department = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne(['department'    =>  $department]);
        $user = UserFactory::createOne(['roles'=>["ROLE_ADMIN"],'employee' => $employee]);

        $this->browser()
            ->actingAs($user)
            ->put('/api/employees/'.$employee->getId(),[
                'json'=>[
                        'name'=>'test'
                    ]
            ])
            ->assertStatus(200);
    }

    public function testVacationLoadWhenEmployeeAdd()
    {
        VacationTypesFactory::createMany(10);
        $department = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne(['department'=>$department]);
        $user = UserFactory::createOne(['roles'=>['ROLE_ADMIN'],'employee' => $employee]);

         $this->browser()
            ->actingAs($user)
            ->post('/api/employees',['json'=>[
                'name'=>'test',
                'surname'=>'test',
                'department' => 'api/departments/'.$department->getId()
            ]
            ])
            ->assertStatus(201);

        $this->browser()
            ->actingAs($user)
            ->get('/api/employees')
            ->assertStatus(200);
    }

    public function testEmployeeUnActive()
    {
        $department = DepartmentFactory::createOne();

        $employee = EmployeeFactory::createOne(['department'    =>  $department]);
        $user = UserFactory::createOne(['roles'=>["ROLE_ADMIN"],'employee' => $employee]);

        $this->browser()
            ->actingAs($user)
            ->put('/api/employees/'.$employee->getId(),[
                'json'=>[
                    'unactive'=> true
                ]
            ])
            ->assertStatus(200);
    }


}