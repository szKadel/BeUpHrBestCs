<?php

namespace App\Tests\Functional\Api\Settings;

use App\Factory\Company\EmployeeFactory;
use App\Factory\Settings\NotificationFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class NotificationResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testNotificationGetResourceTest()
    {
        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['password'=>'pass', 'roles'=>['ROLE_ADMIN'], 'employee' => $employee]);

        $this->browser()
            ->actingAs($user)
            ->get('/api/notifications')
            ->assertStatus(200);

    }
}