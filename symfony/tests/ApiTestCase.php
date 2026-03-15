<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class ApiTestCase extends BaseApiTestCase
{
    use ResetDatabase;

    protected static ?bool $alwaysBootKernel = true;

    protected function apiGet(string $uri)
    {
        return static::createClient()->request('GET', $uri, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    protected function apiPost(string $uri, array $data)
    {
        return static::createClient()->request('POST', $uri, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);
    }

    protected function apiPatch(string $uri, array $data)
    {
        return static::createClient()->request('PATCH', $uri, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => $data,
        ]);
    }

    protected function apiDelete(string $uri)
    {
        return static::createClient()->request('DELETE', $uri, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
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
}
