<?php

namespace App\State\Person;

use App\Dto\Person\PersonPatchDto;
use App\Entity\Person;
use App\Mapper\MapperRegistry;
use App\State\AbstractPatchProcessor;
use App\Write\Person\PersonWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class PersonPatchProcessor extends AbstractPatchProcessor
{
    public function __construct(MapperRegistry                             $mapperRegistry,
                                EntityManagerInterface                     $em,
                                Security                                   $security,
                                private readonly PersonWriteServiceInterface $personWriteService,
    )
    {
        parent::__construct($mapperRegistry, $em, $security);
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonPatchDto) {
            throw new \LogicException('Expected PersonPatchDto.');
        }
    }

    protected function entityClass(): string
    {
        return Person::class;
    }

    protected function patchEntity(mixed $data, object $entity, array $context): void
    {
        \assert($data instanceof PersonPatchDto);
        \assert($entity instanceof Person);

        $this->personWriteService->patch(
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
        return 'A person with the same name already exists.';
    }
}
