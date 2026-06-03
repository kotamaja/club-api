<?php

namespace App\State\Person;

use App\Entity\Person;
use App\State\AbstractDeleteProcessor;
use App\Write\Person\PersonWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class PersonDeleteProcessor extends AbstractDeleteProcessor
{

    public function __construct(EntityManagerInterface                       $em,
                                Security                                     $security,
                                private readonly PersonWriteServiceInterface $personWriteService)
    {
        parent::__construct($em, $security);
    }

    protected function entityClass(): string
    {
        return Person::class;
    }

    protected function deleteEntity(object $entity, array $context): void
    {
        \assert($entity instanceof Person);

        $this->personWriteService->delete($entity, $this->getCurrentConnectionUser());
    }

}
