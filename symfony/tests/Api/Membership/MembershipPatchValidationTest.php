<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\MembershipFactory;

final class MembershipPatchValidationTest extends ApiTestCase
{
    public function testPatchRejectsEndedAtBeforeJoinedAt(): void
    {
        $membership = MembershipFactory::createOne([
            'joinedAt' => new \DateTimeImmutable('2024-06-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'endedAt' => '2024-01-01T00:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsNullJoinedAt(): void
    {
        $membership = MembershipFactory::createOne([
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'joinedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsInvalidJoinedAtFormat(): void
    {
        $membership = MembershipFactory::createOne([
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'joinedAt' => 'not-a-date',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testPatchRejectsInvalidEndedAtFormat(): void
    {
        $membership = MembershipFactory::createOne([
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'endedAt' => 'not-a-date',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }
}
