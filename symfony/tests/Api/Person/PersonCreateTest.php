<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;

final class PersonCreateTest  extends ApiTestCase
{
    public function testCreate(): void
    {
        $response = $this->apiPost('/api/v1/people', [
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com'
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertJsonContains([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->assertArrayHasValidUlid($data, 'id');
    }

}
