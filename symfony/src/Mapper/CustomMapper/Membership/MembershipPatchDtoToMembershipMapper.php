<?php

namespace App\Mapper\CustomMapper\Membership;

use App\Dto\Membership\MembershipPatchDto;
use App\Entity\Membership;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: MembershipPatchDto::class, target: Membership::class)]
final class MembershipPatchDtoToMembershipMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof MembershipPatchDto) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', MembershipPatchDto::class, get_debug_type($source)));
        }

        $membership = $target instanceof Membership ? $target : new Membership();

        if ($source->isJoinedAtProvided()) {
            $membership->setJoinedAt($source->getJoinedAt());
        }

        if ($source->isEndedAtProvided()) {
            $membership->setEndedAt($source->getEndedAt());
        }

        return $membership;
    }
}
