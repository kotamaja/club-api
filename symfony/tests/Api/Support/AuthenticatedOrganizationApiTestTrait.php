<?php

namespace App\Tests\Api\Support;

use App\Entity\ConnectionUser;
use App\Factory\ConnectionUserFactory;
use App\Factory\OrganizationFactory;
use App\Factory\OrganizationUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait AuthenticatedOrganizationApiTestTrait
{
    abstract protected static function getContainer(): ContainerInterface;

    private function createAuthenticatedOrganizationContext(
        string $email = 'admin@example.test',
        string $plainPassword = 'password-123456',
        string $organizationName = 'Test Organization',
        string $organizationSlug = 'test-organization',
        array $organizationRoles = ['ORG_ADMIN'],
    ): AuthenticatedOrganizationContext {
        $connectionUser = $this->createActiveConnectionUserForApiTest(
            email: $email,
            plainPassword: $plainPassword,
        );

        $organization = OrganizationFactory::new()
            ->withNameAndSlug($organizationName, $organizationSlug)
            ->create();

        $organizationUser = OrganizationUserFactory::new()
            ->forConnectionUser($connectionUser)
            ->forOrganization($organization)
            ->withRoles($organizationRoles)
            ->create();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        /** @var JWTTokenManagerInterface $jwtTokenManager */
        $jwtTokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        $jwt = $jwtTokenManager->create($connectionUser);

        return new AuthenticatedOrganizationContext(
            connectionUser: $connectionUser,
            organization: $organization,
            organizationUser: $organizationUser,
            jwt: $jwt,
        );
    }

    private function createActiveConnectionUserForApiTest(
        string $email = 'admin@example.test',
        string $plainPassword = 'password-123456',
    ): ConnectionUser {
        $connectionUser = ConnectionUserFactory::new()
            ->withEmail($email)
            ->create();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $passwordHash = $passwordHasher->hashPassword($connectionUser, $plainPassword);

        $connectionUser->activate($passwordHash);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        return $connectionUser;
    }
}
