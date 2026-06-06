<?php

namespace App\State\PersonContact;

use App\Entity\PersonContact;
use App\State\AbstractDeleteProcessor;
use App\Write\PersonContact\PersonContactWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class PersonContactDeleteProcessor extends AbstractDeleteProcessor
{

    public function __construct(EntityManagerInterface                              $em,
                                Security                                            $security,
                                private readonly PersonContactWriteServiceInterface $personContactWriteService)
    {
        parent::__construct($em, $security);
    }

    protected function entityClass(): string
    {
        return PersonContact::class;
    }

    protected function deleteEntity(object $entity, array $context): void
    {
        \assert($entity instanceof PersonContact);

        $this->personContactWriteService->delete($entity, $this->getCurrentConnectionUser());
    }

}
