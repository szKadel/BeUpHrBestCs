<?php

namespace App\Tests\Functional\Api\Company;

use App\Factory\Company\EmployeeFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class DepartmentResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testDepartmentPost()
    {
        $this->browser()
            ->get('/api/departments')
            ->assertStatus(401);

        $employee = EmployeeFactory::createOne();
        $user = UserFactory::createOne(['employee' => $employee]);

        $this->browser()
            ->actingAs($user)
            ->get('/api/departments')
            ->assertStatus(200)
            ->assertJsonMatches('"hydra:totalItems"', 1);
    }
}