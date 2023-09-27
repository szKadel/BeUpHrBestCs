<?php

namespace App\Tests\Functional\Api\Authentication;

use App\Factory\ApiTokenFactory;
use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class AuthenticationTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testLogin()
    {
        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['password'=>'pass']);

        $this->browser()
            ->post('/login',['json'=>[
                'email'=>$user->getEmail(),
                'password'=>'pas'
            ]
            ])->assertStatus(401);

        $this->browser()
            ->post('/login',['json'=>[
                'email'=>$user->getEmail(),
                'password'=>'pass'
            ]
            ])->assertStatus(200)->assertAuthenticated();
    }

    public function testRegistration()
    {
        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne();
        $token = ApiTokenFactory::createOne();

        $user = UserFactory::createOne(['password'=>'pass', 'roles'=>['ROLE_ADMIN'], 'employee' => null]);

        $this->browser()
            ->actingAs($user)
            ->post(
                '/api/users',
                [
                    'json'=>[
                        'email'=>'test5@test.pl',
                        'password'=>'test',
                        'username'=>'test5'
                    ]
                ]
            )->assertStatus(201);

        $this->browser()
            ->post(
                '/api/users',
                [
                    'json'=>[
                        'email'=>'test@test.pl',
                        'password'=>'test',
                        'username'=>'test'
                    ]
                ]
            )->assertStatus(401);

        $this->browser()
            ->post('/login',['json'=>[
                'email'=>'test5@test.pl',
                'password'=>'test'
            ]
            ])->assertStatus(200);
    }

    public function testCheckUser()
    {
        $department = DepartmentFactory::createMany(5);
        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['password'=>'pass','employee' => null]);

        //check user with employee

        $this->browser()
            ->actingAs($user)
            ->get('/api/getCurrentUser/',[
            ]
            )->assertStatus(200);

        $user = UserFactory::createOne(['password'=>'pass']);

        //check user whit employee
        $this->browser()
            ->actingAs($user)
            ->get('/api/getCurrentUser/',[]
            )->assertStatus(200);

        //check authentication
        $this->browser()
            ->get('/api/getCurrentUser/',[
                ]
            )->assertStatus(401);
    }
}