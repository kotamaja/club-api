<?php

namespace App\State\PersonRelationship;

use ApiPlatform\Metadata\Operation;
use App\Dto\PersonRelationship\PersonRelationshipCreateDto;
use App\Entity\Person;
use App\Entity\PersonRelationship;
use App\Mapper\MapperRegistry;
use App\Repository\PersonRepository;
use App\State\Util\AbstractCreateProcessor;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PersonRelationshipCreateProcessor extends AbstractCreateProcessor
{

    public function __construct(MapperRegistry         $mapperRegistry,
                                EntityManagerInterface $em,
                                private PersonRepository       $personRepository,
    )
    {
        parent::__construct($mapperRegistry, $em);
    }

    protected function entityClass(): string
    {
        return PersonRelationship::class;
    }

    protected function assertInput(mixed $data): void
    {
        if (!$data instanceof PersonRelationshipCreateDto) {
            throw new \LogicException('Expected PersonRelationshipCreateDto.');
        }
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {

        try {
            return parent::process($data, $operation, $uriVariables, $context);
        } catch (UniqueConstraintViolationException) {
            throw new ConflictHttpException('Relationship already exists for this subject, related person and type.');
        }
    }

    protected function beforePersist(mixed $data, object $entity, array $context): void
    {

        if ($data->subjectId === $data->relatedPersonId) {
            throw new UnprocessableEntityHttpException('A person cannot be related to themselves.');
        }

        $subject = $this->personRepository->findOneBy(['publicId' => $data->subjectId]);
        if (!$subject instanceof Person) {
            throw new NotFoundHttpException('Subject person not found.');
        }

        $relatedPerson = $this->personRepository->findOneBy(['publicId' => $data->relatedPersonId]);
        if (!$relatedPerson instanceof Person) {
            throw new NotFoundHttpException('Related person not found.');
        }

        $entity->setSubject($subject);
        $entity->setRelatedPerson($relatedPerson);
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): ?string
    {
        return 'This relationship already exists.';
    }

}
