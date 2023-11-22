<?php

namespace Functional\Api\Authentication;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginTest extends ApiTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testLogin()
    {
        $employee = EmployeeFactory::createOne(['unActive'=>1]);
        $user = UserFactory::createOne(['employee' => $employee,'email'=>"szymonkadelski@gmail.com",'password'=>'test']);

        $this->browser()
            ->post('/login',['json'=>[
                'email'=>'szymonkadelski@gmail.com',
                'password'=>'test'
            ]
            ])->assertStatus(400)->assertAuthenticated();
    }

    public function testGetFile()
    {
        $employee = EmployeeFactory::createOne(['unActive'=>1]);
        $user = UserFactory::createOne(['employee' => $employee,'email'=>"szymonkadelski@gmail.com",'password'=>'test']);

        $this->browser()
            ->post('/login')->assertStatus(401);
    }
}