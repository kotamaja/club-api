<?php

namespace App\State\PersonContact;

use ApiPlatform\Metadata\Operation;
use App\Dto\PersonContact\PersonContactCreateDto;
use App\Entity\Person;
use App\Entity\PersonContact;
use App\Mapper\MapperRegistry;
use App\Repository\PersonRepository;
use App\State\Util\AbstractCreateProcessor;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PersonContactCreateProcessor extends AbstractCreateProcessor
{

    public function __construct(MapperRegistry           $mapperRegistry,
                                EntityManagerInterface   $em,
                                private PersonRepository $personRepository,
    )
    {
        parent::__construct($mapperRegistry, $em);
    }

    protected function entityClass(): string
    {
        return PersonContact::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonContactCreateDto) {
            throw new \LogicException('Expected PersonContactCreateDto.');
        }
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {

        try {
            return parent::process($data, $operation, $uriVariables, $context);
        } catch (UniqueConstraintViolationException) {
            throw new ConflictHttpException('Relationship already exists for this person, contactPerson person and type.');
        }
    }

    protected function beforePersist(mixed $data, object $entity, array $context): void
    {

        if ($data->personId === $data->contactPersonId) {
            throw new UnprocessableEntityHttpException('A person cannot be related to themselves.');
        }

        $person = $this->personRepository->findOneBy(['publicId' => $data->personId]);
        if (!$person instanceof Person) {
            throw new NotFoundHttpException('person not found.');
        }

        $contactPerson = $this->personRepository->findOneBy(['publicId' => $data->contactPersonId]);
        if (!$contactPerson instanceof Person) {
            throw new NotFoundHttpException('Related person not found.');
        }

        $entity->setPerson($person);
        $entity->setContactPerson($contactPerson);
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): ?string
    {
        return 'This relationship already exists.';
    }

}
