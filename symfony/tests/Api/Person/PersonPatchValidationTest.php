<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonFactory;

final class PersonPatchValidationTest extends ApiTestCase
{
    public function testPatchRejectsBlankFirstname(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->apiPatch('/api/v1/people/'.$person->getPublicId(), [
            'firstname' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsBlankLastname(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->apiPatch('/api/v1/people/'.$person->getPublicId(), [
            'lastname' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsBlankEmail(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->apiPatch('/api/v1/people/'.$person->getPublicId(), [
            'email' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsInvalidEmail(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->apiPatch('/api/v1/people/'.$person->getPublicId(), [
            'email' => 'not-an-email',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
