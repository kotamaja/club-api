<?php

namespace App\State\Util;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Mapper\MapperRegistry;
use Doctrine\ORM\EntityManagerInterface;

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

        $this->em->persist($entity);
        $this->em->flush();

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
}
