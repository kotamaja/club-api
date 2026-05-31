<?php

namespace App\Tests\Functional\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ConnectionUser;
use App\Factory\ConnectionUserFactory;
use App\Repository\ConnectionUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ConnectionUserSecurityTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    use Factories;
    use ResetDatabase;
    use AuthenticationTestHelperTrait;

    public function testDisabledUserCannotLogin(): void
    {
        $user = $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $user->disable();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'body',
            ],
        ]);

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode()
        );
    }


    public function testDisabledUserWithExistingJwtIsRejected(): void
    {
        $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $client = static::createClient();

        $loginResponse = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'none',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $loginPayload = $loginResponse->toArray();

        self::assertArrayHasKey('token', $loginPayload);

        $jwt = $loginPayload['token'];

        /** @var ConnectionUserRepository $connectionUserRepository */
        $connectionUserRepository = static::getContainer()->get(ConnectionUserRepository::class);

        $user = $connectionUserRepository->findOneBy([
            'email' => 'admin@example.test',
        ]);

        self::assertNotNull($user);

        $user->disable();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();

        // Vérification optionnelle mais très utile pendant le debug
        $entityManager->clear();

        $disabledUser = $connectionUserRepository->findOneBy([
            'email' => 'admin@example.test',
        ]);

        self::assertNotNull($disabledUser);
        self::assertTrue($disabledUser->isDisabled());

        $protectedResponse = $client->request('GET', '/api/auth/me', [
            'headers' => [
                'accept' =>  'application/json',
                'Authorization' => 'Bearer ' . $jwt,
            ],
        ]);

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $protectedResponse->getStatusCode()
        );
    }

}
