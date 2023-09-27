<?php

namespace App\Factory\Vacation;

use App\Entity\Vacation\VacationStatus;
use App\Repository\VacationStatusRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<VacationStatus>
 *
 * @method        VacationStatus|Proxy create(array|callable $attributes = [])
 * @method static VacationStatus|Proxy createOne(array $attributes = [])
 * @method static VacationStatus|Proxy find(object|array|mixed $criteria)
 * @method static VacationStatus|Proxy findOrCreate(array $attributes)
 * @method static VacationStatus|Proxy first(string $sortedField = 'id')
 * @method static VacationStatus|Proxy last(string $sortedField = 'id')
 * @method static VacationStatus|Proxy random(array $attributes = [])
 * @method static VacationStatus|Proxy randomOrCreate(array $attributes = [])
 * @method static VacationStatusRepository|RepositoryProxy repository()
 * @method static VacationStatus[]|Proxy[] all()
 * @method static VacationStatus[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static VacationStatus[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static VacationStatus[]|Proxy[] findBy(array $attributes)
 * @method static VacationStatus[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static VacationStatus[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class VacationStatusFactory extends ModelFactory
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
            'name' => self::faker()->companySuffix(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(VacationStatus $vacationStatus): void {})
        ;
    }

    protected static function getClass(): string
    {
        return VacationStatus::class;
    }
}
