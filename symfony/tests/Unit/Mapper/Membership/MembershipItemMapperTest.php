<?php

namespace App\Tests\Unit\Mapper\Membership;

use App\Dto\Club\ClubListDto;
use App\Dto\Membership\MembershipItemDto;
use App\Dto\Person\PersonListDto;
use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use App\Mapper\CustomMapper\Membership\MembershipToMembershipItemDtoMapper;
use App\Tests\Support\MapperRegistryMockTrait;
use PHPUnit\Framework\TestCase;

 final class MembershipItemMapperTest extends TestCase
{
    use MapperRegistryMockTrait;

    public function testMapMapsAllFields(): void
    {
        $person = new Person();
        $person->setFirstname('Yves');
        $person->setLastname('Dupont');
        $person->setEmail('yves.dupont@example.com');

        $club = new Club();
        $club->setName('Judo Club');

        $membership = new Membership();
        $membership->setPerson($person);
        $membership->setClub($club);
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(null);

        $personDto = new PersonListDto();
        $personDto->id = $person->getPublicId();
        $personDto->firstname = 'Yves';
        $personDto->lastname = 'Dupont';
        $personDto->email = 'yves.dupont@example.com';

        $clubDto = new ClubListDto();
        $clubDto->id = $club->getPublicId();
        $clubDto->name = 'Judo Club';

        $mapperRegistry = $this->createMapperRegistryMock([
            [$person, PersonListDto::class, $personDto],
            [$club, ClubListDto::class, $clubDto],
        ]);

        $mapper = new MembershipToMembershipItemDtoMapper($mapperRegistry);

        $dto = new MembershipItemDto();

        $result = $mapper->map($membership, $dto);

        $this->assertSame($dto, $result);

        $this->assertSame($membership->getPublicId(), $dto->id);

        $this->assertInstanceOf(PersonListDto::class, $dto->person);
        $this->assertSame($person->getPublicId(), $dto->person->id);
        $this->assertSame('Yves', $dto->person->firstname);
        $this->assertSame('Dupont', $dto->person->lastname);
        $this->assertSame('yves.dupont@example.com', $dto->person->email);

        $this->assertInstanceOf(ClubListDto::class, $dto->club);
        $this->assertSame($club->getPublicId(), $dto->club->id);
        $this->assertSame('Judo Club', $dto->club->name);

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
        $person->setFirstname('Yves');
        $person->setLastname('Dupont');
        $person->setEmail('yves.dupont@example.com');

        $club = new Club();
        $club->setName('Judo Club');

        $membership = new Membership();
        $membership->setPerson($person);
        $membership->setClub($club);
        $membership->setJoinedAt(new \DateTimeImmutable('2024-01-01'));
        $membership->setEndedAt(new \DateTimeImmutable('2024-06-01'));

        $personDto = new PersonListDto();
        $personDto->id = $person->getPublicId();
        $personDto->firstname = 'Yves';
        $personDto->lastname = 'Dupont';
        $personDto->email = 'yves.dupont@example.com';

        $clubDto = new ClubListDto();
        $clubDto->id = $club->getPublicId();
        $clubDto->name = 'Judo Club';

        $mapperRegistry = $this->createMapperRegistryMock([
            [$person, PersonListDto::class, $personDto],
            [$club, ClubListDto::class, $clubDto],
        ]);

        $mapper = new MembershipToMembershipItemDtoMapper($mapperRegistry);

        $dto = new MembershipItemDto();

        $mapper->map($membership, $dto);

        $this->assertFalse($dto->isActive);
        $this->assertEquals(
            new \DateTimeImmutable('2024-06-01'),
            $dto->endedAt
        );
    }
}
