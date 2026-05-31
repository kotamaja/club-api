<?php

namespace App\Factory;

use App\Entity\ConnectionUser;
use App\Enum\UserStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ConnectionUser>
 */
final class ConnectionUserFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return ConnectionUser::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'roles' => [],
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }

    public function withEmail(string $email): static
    {
        return $this->with([
            'email' => $email,
        ]);
    }

    public function active(string $passwordHash): static
    {
        return $this->afterInstantiate(function (ConnectionUser $connectionUser) use ($passwordHash): void {
            $connectionUser->activate($passwordHash);
        });
    }

    public function invited(): static
    {
        return $this->afterInstantiate(function (ConnectionUser $connectionUser): void {
            $connectionUser->setStatus(UserStatus::Invited);
            $connectionUser->setPasswordHash(null);
            $connectionUser->setActivatedAt(null);
        });
    }

    public function disabled(string $passwordHash): static
    {
        return $this->afterInstantiate(function (ConnectionUser $connectionUser) use ($passwordHash): void {
            $connectionUser->activate($passwordHash);
            $connectionUser->disable();
        });
    }
}
