<?php

namespace App\Tests\Functional\Auth;

use App\Entity\ConnectionUser;
use App\Factory\ConnectionUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait AuthenticationTestHelperTrait
{
    abstract protected static function getContainer(): ContainerInterface;

    private function createActiveConnectionUser(
        string $email = 'admin@example.test',
        string $plainPassword = 'password-123456',
    ): ConnectionUser {
        $user = ConnectionUserFactory::new()
            ->withEmail($email)
            ->create();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $passwordHash = $passwordHasher->hashPassword($user, $plainPassword);

        $user->activate($passwordHash);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        return $user;
    }

    /**
     * @param array<int, string> $setCookieHeaders
     */
    private function hasRefreshTokenCookie(array $setCookieHeaders): bool
    {
        foreach ($setCookieHeaders as $setCookieHeader) {
            if (str_starts_with($setCookieHeader, 'refresh_token=')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $setCookieHeaders
     */
    private function extractRefreshTokenFromSetCookieHeaders(array $setCookieHeaders): ?string
    {
        foreach ($setCookieHeaders as $setCookieHeader) {
            if (!str_starts_with($setCookieHeader, 'refresh_token=')) {
                continue;
            }

            $cookieValuePart = explode(';', $setCookieHeader, 2)[0];
            $cookieValue = substr($cookieValuePart, strlen('refresh_token='));

            return urldecode($cookieValue);
        }

        return null;
    }
}
