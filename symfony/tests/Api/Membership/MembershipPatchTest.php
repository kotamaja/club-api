<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\PersonFactory;

final class MembershipPatchTest extends ApiTestCase
{
    public function testPatchCanCloseMembership(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'endedAt' => '2024-06-01T00:00:00+00:00',
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($membership->getPublicId(), $data['id']);
        $this->assertApiDateTimeSameLocal('2024-01-01T00:00:00+00:00', $data['joinedAt']);
        $this->assertApiDateTimeSameLocal('2024-06-01T00:00:00+00:00', $data['endedAt']);
        $this->assertFalse($data['isActive']);
    }

    public function testPatchCanUpdateJoinedAt(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'joinedAt' => '2024-02-01T00:00:00+00:00',
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertApiDateTimeSameLocal('2024-02-01T00:00:00+00:00', $data['joinedAt']);
        $this->assertNull($data['endedAt']);
        $this->assertTrue($data['isActive']);
    }

    public function testPatchCanReactivateMembershipWhenNoOtherActiveMembershipExists(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => new \DateTimeImmutable('2024-06-01T00:00:00+00:00'),
        ]);

        $response = $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'endedAt' => null,
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertNull($data['endedAt']);
        $this->assertTrue($data['isActive']);
    }

    public function testPatchRejectsReactivationWhenAnotherActiveMembershipExists(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $historicalMembership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2023-01-01T00:00:00+00:00'),
            'endedAt' => new \DateTimeImmutable('2023-12-31T00:00:00+00:00'),
        ]);

        $this->apiPatch('/api/v1/memberships/'.$historicalMembership->getPublicId(), [
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
