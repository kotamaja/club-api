<?php

namespace App\State\Person;

use App\Entity\Person;
use App\State\Util\AbstractDeleteProcessor;
use Doctrine\ORM\EntityManagerInterface;

final class PersonDeleteProcessor extends AbstractDeleteProcessor
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function denyReason(object $entity, array $context): ?string
    {
        if (!$entity instanceof Person) {
            throw new \LogicException('Expected Person entity.');
        }

//        // Option 1: count() (recommandé)
//        $count = $this->personRepository->count(['club' => $entity]);
//        if ($count > 0) {
//            return 'Cannot delete club: people still exist.';
//        }

        return null;
    }
}
