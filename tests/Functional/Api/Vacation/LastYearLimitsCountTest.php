<?php

namespace Functional\Api\Vacation;

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

class LastYearLimitsCountTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testYearCount()
    {
        VacationStatusFactory::createOne(['name'=>'Oczekujący']);
        VacationStatusFactory::createOne(['name'=>'Zaplanowany']);

        $vacationType = VacationTypesFactory::createOne(['name'=>'Urlop Wypoczynkowy']);
        $vacationType2 = VacationTypesFactory::createOne(['name'=>"Na żądanie"]);

        $employee = EmployeeFactory::createOne();

        $user = UserFactory::createOne(['employee' => $employee, 'roles'=>['ROLE_USER']]);

        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType, 'daysLimit'=>26]);
        VacationLimitsFactory::createOne(["employee"=>$employee,'vacationType'=>$vacationType2, 'daysLimit'=>4]);

        VacationFactory::createOne(['employee' => $employee, 'type'=>$vacationType]);
        VacationFactory::createOne(['employee' => $employee, 'type'=>$vacationType]);

        $this->browser()
            ->actingAs($user)
            ->get("/Vacations/YearSummation")
            ->dd();
    }

}