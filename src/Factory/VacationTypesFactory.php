<?php

namespace App\Factory;

use App\Entity\Vacation\VacationTypes;
use App\Repository\VacationTypesRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<VacationTypes>
 *
 * @method        VacationTypes|Proxy create(array|callable $attributes = [])
 * @method static VacationTypes|Proxy createOne(array $attributes = [])
 * @method static VacationTypes|Proxy find(object|array|mixed $criteria)
 * @method static VacationTypes|Proxy findOrCreate(array $attributes)
 * @method static VacationTypes|Proxy first(string $sortedField = 'id')
 * @method static VacationTypes|Proxy last(string $sortedField = 'id')
 * @method static VacationTypes|Proxy random(array $attributes = [])
 * @method static VacationTypes|Proxy randomOrCreate(array $attributes = [])
 * @method static VacationTypesRepository|RepositoryProxy repository()
 * @method static VacationTypes[]|Proxy[] all()
 * @method static VacationTypes[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static VacationTypes[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static VacationTypes[]|Proxy[] findBy(array $attributes)
 * @method static VacationTypes[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static VacationTypes[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class VacationTypesFactory extends ModelFactory
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
            'name' => self::faker()->company(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(VacationTypes $vacationTypes): void {})
        ;
    }

    protected static function getClass(): string
    {
        return VacationTypes::class;
    }
}
