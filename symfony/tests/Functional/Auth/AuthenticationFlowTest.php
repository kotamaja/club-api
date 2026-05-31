<?php

namespace App\Tests\Functional\Auth;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\RefreshTokenRepository;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class AuthenticationFlowTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    use Factories;
    use ResetDatabase;
    use AuthenticationTestHelperTrait;

    public function testLoginWithRefreshTokenModeNone(): void
    {
        $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'none',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $payload = $response->toArray();

        self::assertArrayHasKey('token', $payload);
        self::assertIsString($payload['token']);
        self::assertNotSame('', $payload['token']);

        self::assertArrayNotHasKey('refreshToken', $payload);

        $setCookieHeaders = $response->getHeaders(false)['set-cookie'] ?? [];

        self::assertFalse(
            $this->hasRefreshTokenCookie($setCookieHeaders),
            'No refresh_token cookie should be set when refreshTokenMode=none.'
        );

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        self::assertSame(0, $refreshTokenRepository->count([]));
    }


    public function testLoginWithRefreshTokenModeBody(): void
    {
        $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'body',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $payload = $response->toArray();

        self::assertArrayHasKey('token', $payload);
        self::assertIsString($payload['token']);
        self::assertNotSame('', $payload['token']);

        self::assertArrayHasKey('refreshToken', $payload);
        self::assertIsString($payload['refreshToken']);
        self::assertNotSame('', $payload['refreshToken']);

        $setCookieHeaders = $response->getHeaders(false)['set-cookie'] ?? [];

        self::assertFalse(
            $this->hasRefreshTokenCookie($setCookieHeaders),
            'No refresh_token cookie should be set when refreshTokenMode=body.'
        );

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $refreshTokens = $refreshTokenRepository->findAll();

        self::assertCount(1, $refreshTokens);

        $refreshToken = $refreshTokens[0];

        self::assertSame(
            hash('sha256', $payload['refreshToken']),
            $refreshToken->getTokenHash()
        );

        self::assertSame('body', $refreshToken->getMode()->value);
        self::assertFalse($refreshToken->isRevoked());
        self::assertFalse($refreshToken->isExpired());
    }


    public function testLoginWithRefreshTokenModeCookie(): void
    {
        $this->createActiveConnectionUser(
            email: 'admin@example.test',
            plainPassword: 'password-123456',
        );

        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'admin@example.test',
                'password' => 'password-123456',
                'refreshTokenMode' => 'cookie',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $payload = $response->toArray();

        self::assertArrayHasKey('token', $payload);
        self::assertIsString($payload['token']);
        self::assertNotSame('', $payload['token']);

        self::assertArrayNotHasKey('refreshToken', $payload);

        $refreshTokenCookie = $response->getHeaders(false)['set-cookie'] ?? [];

        self::assertTrue(
            $this->hasRefreshTokenCookie($refreshTokenCookie),
            'A refresh_token cookie should be set when refreshTokenMode=cookie.'
        );

        $plainRefreshToken = $this->extractRefreshTokenFromSetCookieHeaders($refreshTokenCookie);

        self::assertNotNull($plainRefreshToken);
        self::assertNotSame('', $plainRefreshToken);

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $refreshTokens = $refreshTokenRepository->findAll();

        self::assertCount(1, $refreshTokens);

        $refreshToken = $refreshTokens[0];

        self::assertSame(
            hash('sha256', $plainRefreshToken),
            $refreshToken->getTokenHash()
        );

        self::assertSame('cookie', $refreshToken->getMode()->value);
        self::assertFalse($refreshToken->isRevoked());
        self::assertFalse($refreshToken->isExpired());

        $setCookieHeader = implode('; ', $refreshTokenCookie);

        self::assertStringContainsString('refresh_token=', $setCookieHeader);
        self::assertStringContainsString('httponly', mb_strtolower($setCookieHeader));
        self::assertStringContainsString('secure', mb_strtolower($setCookieHeader));
        self::assertStringContainsString('samesite=strict', mb_strtolower($setCookieHeader));
    }


    public function testLogoutRevokesRefreshToken(): void
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
                'refreshTokenMode' => 'body',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $loginPayload = $loginResponse->toArray();

        self::assertArrayHasKey('refreshToken', $loginPayload);

        $plainRefreshToken = $loginPayload['refreshToken'];

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $refreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $plainRefreshToken)
        );

        self::assertNotNull($refreshToken);
        self::assertFalse($refreshToken->isRevoked());

        $logoutResponse = $client->request('POST', '/api/auth/logout', [
            'json' => [
                'refreshToken' => $plainRefreshToken,
            ],
        ]);

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $logoutResponse->getStatusCode()
        );

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = static::getContainer()->get(RefreshTokenRepository::class);

        $refreshToken = $refreshTokenRepository->findOneByTokenHash(
            hash('sha256', $plainRefreshToken)
        );

        self::assertNotNull($refreshToken);
        self::assertTrue($refreshToken->isRevoked());
        self::assertSame('logout', $refreshToken->getRevocationReason());

        $refreshAfterLogoutResponse = $client->request('POST', '/api/auth/refresh', [
            'json' => [
                'refreshToken' => $plainRefreshToken,
            ],
        ]);

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $refreshAfterLogoutResponse->getStatusCode()
        );
    }

}
