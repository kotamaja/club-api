<?php

namespace App\State\Util;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

abstract class AbstractDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {}

    final public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
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

        $reason = $this->denyReason($entity, $context);
        if (null !== $reason) {
            throw new ConflictHttpException($reason); // 409
        }

        $this->beforeRemove($entity, $context);

        try {
        $this->em->remove($entity);
        $this->em->flush();

        } catch (ForeignKeyConstraintViolationException $e) {
            $message = $this->foreignKeyConstraintViolationMessage($entity, $context);

            if (null === $message) {
                throw $e;
            }

            throw new ConflictHttpException($message, $e);
        }

        $this->afterRemove($entity, $context);

        return null;
    }

    /**
     * Retourne une string si suppression interdite (message), sinon null.
     * Override pour mettre les règles métier.
     */
    protected function denyReason(object $entity, array $context): ?string
    {
        return null;
    }

    protected function beforeRemove(object $entity, array $context): void {}
    protected function afterRemove(object $entity, array $context): void {}

    protected function foreignKeyConstraintViolationMessage(object $entity, array $context): ?string
    {
        return 'This resource cannot be deleted because it is still referenced by other resources.';
    }

}
