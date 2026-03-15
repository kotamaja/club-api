<?php

namespace App\State\Club;

use App\Entity\Club;
use App\State\Util\AbstractDeleteProcessor;
use Doctrine\ORM\EntityManagerInterface;

final class ClubDeleteProcessor extends AbstractDeleteProcessor
{
    public function __construct(
        EntityManagerInterface $em,
    ) {
        parent::__construct($em);
    }

    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof Club) {
            throw new \LogicException('Expected Club entity.');
        }

//        // Option 1: count() (recommandé)
//        $count = $this->personRepository->count(['club' => $entity]);
//        if ($count > 0) {
//            return 'Cannot delete club: people still exist.';
//        }

        return null;
    }
}
