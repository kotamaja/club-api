<?php

namespace App\State\Util;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Club;
use App\Mapper\MapperRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractPatchProcessor implements ProcessorInterface
{
    use OutputDtoResolverTrait;

    public function __construct(
        protected readonly MapperRegistry $mapperRegistry,
        protected readonly EntityManagerInterface $em,
    ) {}

    final public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->assertInput($data);

        $entity = $context['previous_data'] ?? null;
        if (!\is_object($entity)) {
            throw new \LogicException('Expected previous_data entity. Ensure operation has read: true.');
        }

        if (!method_exists($entity, 'getId')) {
            throw new \LogicException(sprintf('Expected previous_data to have getId(), got %s.', $entity::class));
        }

        $id = $entity->getId();
        if (null === $id) {
            throw new \LogicException(sprintf('Expected previous_data to have a non-null id (%s).', $entity::class));
        }

        if (!$this->em->contains($entity)) {
            $entity = $this->em->getReference($entity::class, $id);
        }

        $this->beforeMap($data, $entity, $context);

        $originalObjectId = spl_object_id($entity);

        $this->mapperRegistry->map($data, $entity);

        if (spl_object_id($entity) !== $originalObjectId) {
            throw new \LogicException('Patch mapper must mutate entity, not replace it.');
        }

        $this->afterMap($data, $entity, $context);

        $this->em->flush();

        $outputDto = $this->resolveOutputDto($operation);
        return $this->mapperRegistry->map($entity, $outputDto);
    }

    abstract protected function assertInput(mixed $data): void;

    protected function beforeMap(mixed $data, object $entity, array $context): void {}
    protected function afterMap(mixed $data, object $entity, array $context): void {}
}
