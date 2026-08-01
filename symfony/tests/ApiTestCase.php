<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use App\Core\Enum\ServicePlan;
use App\Entity\ConnectionUser;
use App\Factory\ConnectionUserFactory;
use App\Factory\OrganizationFactory;
use App\Factory\OrganizationUserFactory;
use App\Factory\PersonFactory;
use App\Tests\Api\Support\AuthenticatedOrganizationContext;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class ApiTestCase extends BaseApiTestCase
{
    use ResetDatabase;

    protected static ?bool $alwaysBootKernel = true;

    private ?AuthenticatedOrganizationContext $authenticatedOrganizationContext = null;

    protected function apiGet(string $uri, array $headers = [])
    {
        return static::createClient()->request('GET', $uri, [
            'headers' => $this->apiHeaders($headers),
        ]);
    }

    protected function apiPost(string $uri, array $data, array $headers = [])
    {
        return static::createClient()->request('POST', $uri, [
            'headers' => $this->apiHeaders([
                'Content-Type' => 'application/json',
                ...$headers,
            ]),
            'json' => $data,
        ]);
    }

    protected function apiPatch(string $uri, array $data, array $headers = [])
    {
        return static::createClient()->request('PATCH', $uri, [
            'headers' => $this->apiHeaders([
                'Content-Type' => 'application/merge-patch+json',
                ...$headers,
            ]),
            'json' => $data,
        ]);
    }

    protected function apiDelete(string $uri, array $headers = [])
    {
        return static::createClient()->request('DELETE', $uri, [
            'headers' => $this->apiHeaders($headers),
        ]);
    }

    /**
     * Sends a public POST request without authentication or organization headers.
     */
    protected function apiPublicPost(string $uri, array $data, array $headers = [])
    {
        return static::createClient()->request('POST', $uri, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                ...$headers,
            ],
            'json' => $data,
        ]);
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    protected function apiHeaders(array $headers = []): array
    {
        $context = $this->getAuthenticatedOrganizationContext();

        return [
            'Accept' => 'application/json',
            ...$context->headers(),
            ...$headers,
        ];
    }

    protected function getAuthenticatedOrganizationContext(ServicePlan $servicePlan = ServicePlan::Community, bool $includePerson = false): AuthenticatedOrganizationContext
    {
        if ($this->authenticatedOrganizationContext !== null) {
            return $this->authenticatedOrganizationContext;
        }

        return $this->authenticatedOrganizationContext = $this->createAuthenticatedOrganizationContext(servicePlan: $servicePlan, includePerson: $includePerson);
    }

    protected function createAuthenticatedOrganizationContext(
        string      $email = 'admin@example.test',
        string      $plainPassword = 'password-123456',
        string      $organizationName = 'Test Organization',
        string      $organizationSlug = 'test-organization',
        array       $organizationRoles = ['ORG_ADMIN'],
        bool        $includePerson = false,
        ServicePlan $servicePlan = ServicePlan::Community
    ): AuthenticatedOrganizationContext
    {
        $connectionUser = $this->createActiveConnectionUserForApiTest(
            email: $email,
            plainPassword: $plainPassword,
        );

        $organization = OrganizationFactory::new()
            ->withNameAndSlug($organizationName, $organizationSlug)
            ->withServicePlan($servicePlan)
            ->create();

        $person = null;
        if ($includePerson) {
            $person = PersonFactory::new()
                ->forOrganization($organization)
                ->create();
        }

        $organizationUser = OrganizationUserFactory::new()
            ->forConnectionUser($connectionUser)
            ->forOrganization($organization)
            ->forPerson($person)
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
            person: $person,
            jwt: $jwt,
        );
    }

    protected function createActiveConnectionUserForApiTest(
        string $email,
        string $plainPassword,
    ): ConnectionUser
    {
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

    protected function assertValidUlid(string $value): void
    {
        $this->assertTrue(
            Ulid::isValid($value),
            sprintf('Failed asserting that "%s" is a valid ULID.', $value)
        );
    }

    protected function assertArrayHasValidUlid(array $data, string $key): void
    {
        $this->assertArrayHasKey($key, $data);
        $this->assertIsString($data[$key]);
        $this->assertValidUlid($data[$key]);
    }

    protected function assertApiDateTimeSameLocal(string $expected, string $actual): void
    {
        $this->assertSame(
            (new DateTimeImmutable($expected))->format('Y-m-d H:i:s'),
            (new DateTimeImmutable($actual))->format('Y-m-d H:i:s')
        );
    }
}
