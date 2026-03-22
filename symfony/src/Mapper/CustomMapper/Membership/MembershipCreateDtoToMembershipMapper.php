<?php

namespace App\Mapper\CustomMapper\Membership;

use App\Dto\Membership\MembershipCreateDto;
use App\Entity\Membership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: MembershipCreateDto::class, target: Membership::class)]
final class MembershipCreateDtoToMembershipMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof MembershipCreateDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', MembershipCreateDto::class, get_debug_type($source)));
        }

        $membership = $target instanceof Membership ? $target : new Membership();

        $membership->setJoinedAt($source->joinedAt);
        $membership->setEndedAt($source->endedAt);

        return $membership;
    }
}
