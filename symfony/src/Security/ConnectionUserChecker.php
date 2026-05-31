<?php

namespace App\Security;

use App\Entity\ConnectionUser;
use App\Enum\UserStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ConnectionUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof ConnectionUser) {
            return;
        }

        if ($user->getStatus() === UserStatus::Disabled) {
            throw new CustomUserMessageAccountStatusException('This account is disabled.');
        }

        if ($user->getStatus() === UserStatus::Invited) {
            throw new CustomUserMessageAccountStatusException('This account has not been activated yet.');
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('This account is not active.');
        }

        if ($user->getPassword() === null) {
            throw new CustomUserMessageAccountStatusException('This account has no password configured.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof ConnectionUser) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('This account is not active.');
        }
    }
}
