<?php

namespace App\Factory\Settings;

use App\Entity\Settings\Notification;
use App\Repository\Settings\NotificationRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Notification>
 *
 * @method        Notification|Proxy create(array|callable $attributes = [])
 * @method static Notification|Proxy createOne(array $attributes = [])
 * @method static Notification|Proxy find(object|array|mixed $criteria)
 * @method static Notification|Proxy findOrCreate(array $attributes)
 * @method static Notification|Proxy first(string $sortedField = 'id')
 * @method static Notification|Proxy last(string $sortedField = 'id')
 * @method static Notification|Proxy random(array $attributes = [])
 * @method static Notification|Proxy randomOrCreate(array $attributes = [])
 * @method static NotificationRepository|RepositoryProxy repository()
 * @method static Notification[]|Proxy[] all()
 * @method static Notification[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Notification[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Notification[]|Proxy[] findBy(array $attributes)
 * @method static Notification[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Notification[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class NotificationFactory extends ModelFactory
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
            'notificate_admin_on_accept_vacation' => 1,
            'notificate_department_mod_on_created_vacation' => 1,
            'notificate_replacement_user' => 1,
            'notificate_user_on_vacation_request_accept' => 1
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Notification $notification): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Notification::class;
    }
}
