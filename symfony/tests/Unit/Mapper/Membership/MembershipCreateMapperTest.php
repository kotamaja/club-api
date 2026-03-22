<?php

namespace App\Tests\Unit\Mapper\Membership;

use App\Dto\Membership\MembershipCreateDto;
use App\Entity\Membership;
use App\Mapper\CustomMapper\Membership\MembershipCreateDtoToMembershipMapper;
use PHPUnit\Framework\TestCase;

final class MembershipCreateMapperTest extends TestCase
{
    public function testMapSetsDates(): void
    {
        $dto = new MembershipCreateDto();
        $dto->joinedAt = new \DateTimeImmutable('2024-01-01');
        $dto->endedAt = new \DateTimeImmutable('2024-12-31');

        $mapper = new MembershipCreateDtoToMembershipMapper();

        $membership = new Membership();

        $result = $mapper->map($dto, $membership);

        $this->assertSame($membership, $result);
        $this->assertEquals($dto->joinedAt, $membership->getJoinedAt());
        $this->assertEquals($dto->endedAt, $membership->getEndedAt());
    }

    public function testMapWithNullEndedAtCreatesActiveMembership(): void
    {
        $dto = new MembershipCreateDto();
        $dto->joinedAt = new \DateTimeImmutable('2024-01-01');
        $dto->endedAt = null;

        $mapper = new MembershipCreateDtoToMembershipMapper();

        $membership = new Membership();

        $mapper->map($dto, $membership);

        $this->assertNull($membership->getEndedAt());
        $this->assertTrue($membership->isActive());
    }
}
