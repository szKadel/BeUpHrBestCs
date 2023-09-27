<?php

namespace App\Factory;
use App\Entity\User;
use App\Factory\Company\EmployeeFactory;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;
/**
 * @extends ModelFactory<User>
 */
final class UserFactory extends ModelFactory
{
    const USERNAMES = [
        'Szymon',
        'Karol',
        'Ostap',
        'Vadim',
        'Dawid',
        'Krzysztof',
        'Sandra',
    ];
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    )
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
            'email' => self::faker()->email(),
            'password' => 'password',
            'username' => self::faker()->randomElement(self::USERNAMES) . self::faker()->randomNumber(3),
            'employee' => EmployeeFactory::random()
        ];
    }
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(User $user): void {
                $user->setPassword($this->passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                ));
            })
            ;
    }
    protected static function getClass(): string
    {
        return User::class;
    }
}