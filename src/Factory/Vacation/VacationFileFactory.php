<?php

namespace App\Factory\Vacation;

use App\Entity\Vacation\VacationFile;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<VacationFile>
 *
 * @method        VacationFile|Proxy create(array|callable $attributes = [])
 * @method static VacationFile|Proxy createOne(array $attributes = [])
 * @method static VacationFile|Proxy find(object|array|mixed $criteria)
 * @method static VacationFile|Proxy findOrCreate(array $attributes)
 * @method static VacationFile|Proxy first(string $sortedField = 'id')
 * @method static VacationFile|Proxy last(string $sortedField = 'id')
 * @method static VacationFile|Proxy random(array $attributes = [])
 * @method static VacationFile|Proxy randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static VacationFile[]|Proxy[] all()
 * @method static VacationFile[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static VacationFile[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static VacationFile[]|Proxy[] findBy(array $attributes)
 * @method static VacationFile[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static VacationFile[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class VacationFileFactory extends ModelFactory
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
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(VacationFile $vacationFile): void {})
        ;
    }

    protected static function getClass(): string
    {
        return VacationFile::class;
    }
}
