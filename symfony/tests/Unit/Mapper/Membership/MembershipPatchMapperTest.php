<?php

namespace App\Tests\Unit\Mapper\Membership;

use App\Dto\Membership\MembershipPatchDto;
use App\Entity\Membership;
use App\Mapper\CustomMapper\Membership\MembershipPatchDtoToMembershipMapper;
use PHPUnit\Framework\TestCase;

final class MembershipPatchMapperTest extends TestCase
{
    public function testMapUpdatesOnlyProvidedFields(): void
    {
        $membership = new Membership();
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(null);

        $dto = new MembershipPatchDto();
        $dto->setEndedAt(new \DateTimeImmutable('2024-06-01'));

        $mapper = new MembershipPatchDtoToMembershipMapper();

        $mapper->map($dto, $membership);

        $this->assertEquals(
            new \DateTimeImmutable('2024-01-01'),
            $membership->getJoinedAt()
        );

        $this->assertEquals(
            new \DateTimeImmutable('2024-06-01'),
            $membership->getEndedAt()
        );
    }

    public function testMapCanReactivateMembership(): void
    {
        $membership = new Membership();
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(new \DateTimeImmutable('2024-06-01'));

        $dto = new MembershipPatchDto();
        $dto->setEndedAt(null);

        $mapper = new MembershipPatchDtoToMembershipMapper();

        $mapper->map($dto, $membership);

        $this->assertNull($membership->getEndedAt());
        $this->assertTrue($membership->isActive());
    }

    public function testMapDoesNothingWhenNoFieldProvided(): void
    {
        $membership = new Membership();
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(null);

        $dto = new MembershipPatchDto();

        $mapper = new MembershipPatchDtoToMembershipMapper();

        $mapper->map($dto, $membership);

        $this->assertEquals(
            new \DateTimeImmutable('2024-01-01'),
            $membership->getJoinedAt()
        );

        $this->assertNull($membership->getEndedAt());
    }
}
