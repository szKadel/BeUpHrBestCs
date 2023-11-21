<?php

namespace App\Factory;

use App\Entity\EmployeeExtendedAccesses;
use App\Factory\Company\DepartmentFactory;
use App\Factory\Company\EmployeeFactory;
use App\Repository\EmployeeExtendedAccessesRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<EmployeeExtendedAccesses>
 *
 * @method        EmployeeExtendedAccesses|Proxy create(array|callable $attributes = [])
 * @method static EmployeeExtendedAccesses|Proxy createOne(array $attributes = [])
 * @method static EmployeeExtendedAccesses|Proxy find(object|array|mixed $criteria)
 * @method static EmployeeExtendedAccesses|Proxy findOrCreate(array $attributes)
 * @method static EmployeeExtendedAccesses|Proxy first(string $sortedField = 'id')
 * @method static EmployeeExtendedAccesses|Proxy last(string $sortedField = 'id')
 * @method static EmployeeExtendedAccesses|Proxy random(array $attributes = [])
 * @method static EmployeeExtendedAccesses|Proxy randomOrCreate(array $attributes = [])
 * @method static EmployeeExtendedAccessesRepository|RepositoryProxy repository()
 * @method static EmployeeExtendedAccesses[]|Proxy[] all()
 * @method static EmployeeExtendedAccesses[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static EmployeeExtendedAccesses[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static EmployeeExtendedAccesses[]|Proxy[] findBy(array $attributes)
 * @method static EmployeeExtendedAccesses[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static EmployeeExtendedAccesses[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class EmployeeExtendedAccessesFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'department' => DepartmentFactory::new(),
            'employee' => EmployeeFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(EmployeeExtendedAccesses $employeeExtendedAccesses): void {})
        ;
    }

    protected static function getClass(): string
    {
        return EmployeeExtendedAccesses::class;
    }
}
