<?php

namespace App\State\Util;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Mapper\MapperRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

abstract class AbstractCreateProcessor implements ProcessorInterface
{
    use OutputDtoResolverTrait;

    public function __construct(
        protected readonly MapperRegistry         $mapperRegistry,
        protected readonly EntityManagerInterface $em,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->assertInput($data);

        $entityClass = $this->entityClass();
        $entity = $this->mapperRegistry->map($data, $entityClass);

        $this->beforePersist($data, $entity, $context);

        try {
            $this->em->persist($entity);
            $this->em->flush();
        }
        catch(UniqueConstraintViolationException $e) {
            $message = $this->uniqueConstraintViolationMessage($data, $entity, $context);

            if (null === $message) {
                throw $e;
            }

            throw new ConflictHttpException($message, $e);
        }

        $this->afterPersist($data, $entity, $context);

        $outputDto = $this->resolveOutputDto($operation);
        return $this->mapperRegistry->map($entity, $outputDto);
    }

    /** @return class-string */
    abstract protected function entityClass(): string;

    abstract protected function assertInput(mixed $data): void;

    protected function beforePersist(mixed $data, object $entity, array $context): void
    {
    }

    protected function afterPersist(mixed $data, object $entity, array $context): void
    {
    }

    protected function uniqueConstraintViolationMessage(mixed $data, object $entity, array $context): ?string
    {
        return 'A resource with the same unique values already exists.';
    }
}
