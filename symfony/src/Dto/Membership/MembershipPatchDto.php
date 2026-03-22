<?php

namespace App\Dto\Membership;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class MembershipPatchDto
{
    private bool $joinedAtProvided = false;
    private ?\DateTimeImmutable $joinedAt = null;

    private bool $endedAtProvided = false;
    private ?\DateTimeImmutable $endedAt = null;

    public function setJoinedAt(?\DateTimeImmutable $joinedAt): void
    {
        $this->joinedAtProvided = true;
        $this->joinedAt = $joinedAt;
    }

    public function isJoinedAtProvided(): bool
    {
        return $this->joinedAtProvided;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): void
    {
        $this->endedAtProvided = true;
        $this->endedAt = $endedAt;
    }

    public function isEndedAtProvided(): bool
    {
        return $this->endedAtProvided;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->joinedAtProvided && null === $this->joinedAt) {
            $context
                ->buildViolation('This value should not be null.')
                ->atPath('joinedAt')
                ->addViolation();
        }
    }
}
