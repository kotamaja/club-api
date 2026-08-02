<?php

namespace App\Tests\Functional\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ConnectionUser;
use App\Factory\ConnectionUserFactory;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Component\BrowserKit\Cookie;

final class RefreshTokenFlowTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    use Factories;
    use ResetDatabase;
    use AuthenticationTestHelperTrait;

    public function testRefreshWithBodyModeRotatesRefreshToken(): void
    {
        $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $client = static::createClient();

        $loginResponse = $client->request('POST', '/api/v1/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'body',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $loginPayload = $loginResponse->toArray();

        self::assertArrayHasKey('token', $loginPayload);
        self::assertArrayHasKey('refreshToken', $loginPayload);

        $oldPlainRefreshToken = $loginPayload['refreshToken'];

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $oldRefreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $oldPlainRefreshToken)
        );

        self::assertNotNull($oldRefreshToken);
        self::assertFalse($oldRefreshToken->isRevoked());
        self::assertSame('body', $oldRefreshToken->getMode()->value);

        $refreshResponse = $client->request('POST', '/api/v1/auth/refresh', [
            'json' => [
                'refreshToken' => $oldPlainRefreshToken,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $refreshPayload = $refreshResponse->toArray();

        self::assertArrayHasKey('token', $refreshPayload);
        self::assertIsString($refreshPayload['token']);
        self::assertNotSame('', $refreshPayload['token']);

        self::assertArrayHasKey('refreshToken', $refreshPayload);
        self::assertIsString($refreshPayload['refreshToken']);
        self::assertNotSame('', $refreshPayload['refreshToken']);

        $newPlainRefreshToken = $refreshPayload['refreshToken'];

        self::assertNotSame($oldPlainRefreshToken, $newPlainRefreshToken);


        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $oldRefreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $oldPlainRefreshToken)
        );

        self::assertNotNull($oldRefreshToken);
        self::assertTrue($oldRefreshToken->isRevoked());
        self::assertSame('rotated', $oldRefreshToken->getRevocationReason());
        self::assertNotNull($oldRefreshToken->getReplacedBy());

        $newRefreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $newPlainRefreshToken)
        );

        self::assertNotNull($newRefreshToken);
        self::assertFalse($newRefreshToken->isRevoked());
        self::assertFalse($newRefreshToken->isExpired());
        self::assertSame('body', $newRefreshToken->getMode()->value);

        $reuseOldTokenResponse = $client->request('POST', '/api/v1/auth/refresh', [
            'json' => [
                'refreshToken' => $oldPlainRefreshToken,
            ],
        ]);

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $reuseOldTokenResponse->getStatusCode()
        );
    }

    public function testRefreshWithCookieModeRotatesRefreshToken(): void
    {
        $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $client = static::createClient();

        $loginResponse = $client->request('POST', '/api/v1/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'cookie',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $loginPayload = $loginResponse->toArray();

        self::assertArrayHasKey('token', $loginPayload);
        self::assertArrayNotHasKey('refreshToken', $loginPayload);

        $loginSetCookieHeaders = $loginResponse->getHeaders(false)['set-cookie'] ?? [];
        $oldPlainRefreshToken = $this->extractRefreshTokenFromSetCookieHeaders($loginSetCookieHeaders);

        self::assertNotNull($oldPlainRefreshToken);
        self::assertNotSame('', $oldPlainRefreshToken);

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $oldRefreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $oldPlainRefreshToken)
        );

        self::assertNotNull($oldRefreshToken);
        self::assertFalse($oldRefreshToken->isRevoked());
        self::assertSame('cookie', $oldRefreshToken->getMode()->value);

        $client->getCookieJar()->set(new Cookie(
            'refresh_token',
            $oldPlainRefreshToken,
            null,
            '/api/v1/auth',
        ));

        $refreshResponse = $client->request('POST', '/api/v1/auth/refresh');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $refreshPayload = $refreshResponse->toArray();

        self::assertArrayHasKey('token', $refreshPayload);
        self::assertIsString($refreshPayload['token']);
        self::assertNotSame('', $refreshPayload['token']);

        self::assertArrayNotHasKey('refreshToken', $refreshPayload);

        $refreshSetCookieHeaders = $refreshResponse->getHeaders(false)['set-cookie'] ?? [];
        $newPlainRefreshToken = $this->extractRefreshTokenFromSetCookieHeaders($refreshSetCookieHeaders);

        self::assertNotNull($newPlainRefreshToken);
        self::assertNotSame('', $newPlainRefreshToken);
        self::assertNotSame($oldPlainRefreshToken, $newPlainRefreshToken);

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $oldRefreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $oldPlainRefreshToken)
        );

        self::assertNotNull($oldRefreshToken);
        self::assertTrue($oldRefreshToken->isRevoked());
        self::assertSame('rotated', $oldRefreshToken->getRevocationReason());
        self::assertNotNull($oldRefreshToken->getReplacedBy());

        $newRefreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $newPlainRefreshToken)
        );

        self::assertNotNull($newRefreshToken);
        self::assertFalse($newRefreshToken->isRevoked());
        self::assertFalse($newRefreshToken->isExpired());
        self::assertSame('cookie', $newRefreshToken->getMode()->value);


        $oldTokenClient = static::createClient();

        $oldTokenClient->getCookieJar()->set(new Cookie(
            'refresh_token',
            $oldPlainRefreshToken,
            null,
            '/api/v1/auth',
        ));

        $reuseOldTokenResponse = $oldTokenClient->request('POST', '/api/v1/auth/refresh');
        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $reuseOldTokenResponse->getStatusCode()
        );
    }

}
