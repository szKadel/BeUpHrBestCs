<?php

namespace Functional\Api\Authentication;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class ResetPasswordTest extends ApiTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testResetPassword()
    {
        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['email'=>'szymon@beupsoft.pl', 'roles'=>['ROLE_ADMIN']]);

        $this->browser()
            ->post('/api/user/resetPassword',['json'=>[
                'email' => 'szymon@beupsoft.pl'
            ]
            ])->dd();
    }
}