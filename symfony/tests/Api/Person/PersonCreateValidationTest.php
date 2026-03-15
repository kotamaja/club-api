<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;

final class PersonCreateValidationTest extends ApiTestCase
{
    public function testCreateRejectsBlankFirstname(): void
    {
        $this->apiPost('/api/v1/people', [
            'firstname' => '',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsBlankLastname(): void
    {
        $this->apiPost('/api/v1/people', [
            'firstname' => 'Yves',
            'lastname' => '',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsBlankEmail(): void
    {
        $this->apiPost('/api/v1/people', [
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsInvalidEmail(): void
    {
        $this->apiPost('/api/v1/people', [
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'not-an-email',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
