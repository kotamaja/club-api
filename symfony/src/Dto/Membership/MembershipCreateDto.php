<?php

namespace App\Dto\Membership;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class MembershipCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $personId;

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $clubId;

    #[Assert\NotNull]
    public \DateTimeImmutable $joinedAt;

    public ?\DateTimeImmutable $endedAt = null;

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if (null === $this->endedAt) {
            return;
        }

        if ($this->endedAt < $this->joinedAt) {
            $context
                ->buildViolation('The end date must be greater than or equal to the join date.')
                ->atPath('endedAt')
                ->addViolation();
        }
    }
}
