<?php

namespace App\State\PersonContact;

use App\Dto\PersonContact\PersonContactPatchDto;
use App\Entity\PersonContact;
use App\Mapper\MapperRegistry;
use App\State\AbstractPatchProcessor;
use App\Write\PersonContact\PersonContactWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class PersonContactPatchProcessor extends AbstractPatchProcessor
{
    public function __construct(MapperRegistry                                      $mapperRegistry,
                                EntityManagerInterface                              $em,
                                Security                                            $security,
                                private readonly PersonContactWriteServiceInterface $personContactWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonContactPatchDto) {
            throw new \LogicException('Expected PersonContactPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return PersonContact::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof PersonContactPatchDto);
        \assert($entity instanceof PersonContact);

        $this->personContactWriteService->patch(
            $data,
            $entity,
            $this->getCurrentConnectionUser(),
        );
    }

    protected function uniqueConstraintViolationMessage(
        mixed  $data,
        object $entity,
        array  $context,
    ): string
    {
        return 'This relationship already exists for this person, contact person and type.';
    }
}
