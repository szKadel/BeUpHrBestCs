<?php

namespace App\Factory\Vacation;

use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\VacationStatus;
use App\Factory\Company\EmployeeFactory;
use App\Factory\VacationTypesFactory;
use App\Repository\EmployeeVacationLimitRepository;
use App\Repository\VacationStatusRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<VacationLimits>
 *
 * @method        VacationLimits|Proxy create(array|callable $attributes = [])
 * @method static VacationLimits|Proxy createOne(array $attributes = [])
 * @method static VacationLimits|Proxy find(object|array|mixed $criteria)
 * @method static VacationLimits|Proxy findOrCreate(array $attributes)
 * @method static VacationLimits|Proxy first(string $sortedField = 'id')
 * @method static VacationLimits|Proxy last(string $sortedField = 'id')
 * @method static VacationLimits|Proxy random(array $attributes = [])
 * @method static VacationLimits|Proxy randomOrCreate(array $attributes = [])
 * @method static EmployeeVacationLimitRepository|RepositoryProxy repository()
 * @method static VacationLimits[]|Proxy[] all()
 * @method static VacationLimits[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static VacationLimits[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static VacationLimits[]|Proxy[] findBy(array $attributes)
 * @method static VacationLimits[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static VacationLimits[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class VacationLimitsFactory extends ModelFactory
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
            'daysLimit' => self::faker()->randomNumber(),
            'employee' => EmployeeFactory::new(),
            'vacationType' => VacationTypesFactory::new()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(VacationLimits $vacationLimits): void {})
        ;
    }

    protected static function getClass(): string
    {
        return VacationLimits::class;
    }
}
