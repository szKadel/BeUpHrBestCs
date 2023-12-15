<?php

namespace App\Controller;

use App\Controller\Presist\DepartmentPresist;
use App\Controller\Presist\EmployeePresist;
use App\Entity\Company\Department;
use App\Entity\Company\Employee;
use App\Repository\DepartmentRepository;
use App\Repository\EmployeeRepository;
use App\Service\BitrixService;
use App\Service\Vacation\VacationLimitsAdd;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// TODO Make BitrixLib as an bundle
class BitrixController extends AbstractController
{
    #[Route('/bitrix/migration/users')]
    #[IsGranted('ROLE_ADMIN')]
    public function migrateUsers(
        BitrixService $bitrixService,
        EmployeePresist $employeePresist,
        DepartmentRepository $departmentRepository,
        EmployeeRepository $userRepository,
        VacationLimitsAdd $vacationLimitsAdd
    ):JsonResponse
    {
        $result = $bitrixService->call('user.get',[]);

        $final_result = [
            'result' => false,
            'elements'=> 0
        ];

        $countBatches = ceil($result['total'] / 50);

        $start = 0;
        $i = 0;

        while($countBatches > 0) {
            $result = $bitrixService->call('user.get?start='.$start, []);
            $final_result[] = 'user.get?start='.$start;
            foreach ($result["result"] as $elemnt) {

                if($userRepository->findOneByBitrixId($elemnt["ID"])!== null){
                    $final_result[] = $elemnt["ID"];
                }else {
                    $user = new Employee();
                    $user->setName($elemnt["NAME"]);
                    $user->setSurname($elemnt["LAST_NAME"]);
                    $user->setEmail($elemnt["EMAIL"]);
                    $user->setBitrixId($elemnt["ID"]);
                    $user->setDepartment($departmentRepository->findOneByBitrixIdField($elemnt['UF_DEPARTMENT'][0]));
                    $employeePresist->add($user);
                    if($user?->getId()) {
                        $vacationLimitsAdd->addLimitsForNewEmployee($user);
                    }
                    $final_result[] = $i++;
                }
            }
            $countBatches--;
            $start +=50;

            $final_result['result'] = true;
        }

        return new JsonResponse($final_result);
    }

    #[Route('/bitrix/migration/departments')]
    #[IsGranted('ROLE_ADMIN')]
    public function migrateDepartments(BitrixService $bitrixService,DepartmentRepository $departmentRepository, DepartmentPresist $departmentPresist):JsonResponse
    {
        $result = $bitrixService->call('department.get',[]);

        $final_result = [
            'result' => false,
            'elements'=> 0
        ];

        $countBatches = ceil($result['total'] / 50) + 1;
        $i = 0;
        $start = 0;
        while($countBatches > 0) {
            $result = $bitrixService->call('department.get?start='.$start, []);

            foreach ($result["result"] as $elemnt) {
                if($departmentRepository->findOneByBitrixIdField($elemnt["ID"])!== null){
                    $final_result[] = $elemnt["ID"];
                }else {
                    $department = new Department();
                    $department->setName($elemnt["NAME"]);
                    $department->setBitrixId($elemnt["ID"]);
                    $departmentPresist->add($department);
                    $final_result[] = $i++;
                }
            }
            $countBatches--;
            $start += 50;
            $final_result['result'] = true;
        }

        return new JsonResponse($final_result);
    }

    public function migrate()
    {

    }
}