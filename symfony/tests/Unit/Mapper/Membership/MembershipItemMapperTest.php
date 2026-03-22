<?php

namespace App\Tests\Unit\Mapper\Membership;

use App\Dto\Membership\MembershipItemDto;
use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use App\Mapper\CustomMapper\Membership\MembershipToMembershipItemDtoMapper;
use PHPUnit\Framework\TestCase;

final class MembershipItemMapperTest extends TestCase
{
    public function testMapMapsAllFields(): void
    {
        $person = new Person();
        $club = new Club();

        // ⚠️ important : publicId
        $personPublicId = $person->getPublicId();
        $clubPublicId = $club->getPublicId();

        $membership = new Membership();
        $membership->setPerson($person);
        $membership->setClub($club);
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(null);

        $mapper = new MembershipToMembershipItemDtoMapper();

        $dto = new MembershipItemDto();

        $result = $mapper->map($membership, $dto);

        $this->assertSame($dto, $result);

        $this->assertSame($membership->getPublicId(), $dto->id);
        $this->assertSame($personPublicId, $dto->personId);
        $this->assertSame($clubPublicId, $dto->clubId);

        $this->assertEquals(
            new \DateTimeImmutable('2024-01-01'),
            $dto->joinedAt
        );

        $this->assertNull($dto->endedAt);
        $this->assertTrue($dto->isActive);
    }

    public function testMapInactiveMembership(): void
    {
        $person = new Person();
        $club = new Club();

        $membership = new Membership();
        $membership->setPerson($person);
        $membership->setClub($club);
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(new \DateTimeImmutable('2024-06-01'));

        $mapper = new MembershipToMembershipItemDtoMapper();

        $dto = new MembershipItemDto();

        $mapper->map($membership, $dto);

        $this->assertFalse($dto->isActive);
    }
}
