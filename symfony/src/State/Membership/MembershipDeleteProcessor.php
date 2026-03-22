<?php

namespace App\State\Membership;

use App\Entity\Membership;
use App\State\Util\AbstractDeleteProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class MembershipDeleteProcessor extends AbstractDeleteProcessor
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof Membership) {
            throw new \LogicException('Expected Membership entity.');
        }

        if (null !== $entity->getEndedAt()) {
            return 'Cannot delete a membership that has already ended. Use it as historical data.';
        }

        return null;
    }


}
