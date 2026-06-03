<?php

namespace App\State;


use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use App\Write\Exception\ResourceConflictException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class AbstractDeleteProcessor implements ProcessorInterface
{

    use ProcessorActorTrait;
    use PreviousDataEntityTrait;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Security               $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $entity = $this->getEntityFromContext($context, $this->entityClass());

        try {
            $this->deleteEntity($entity, $context);

            $this->em->flush();
        } catch (ReferencedResourceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ResourceConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        } catch (BusinessRuleViolationException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new ConflictHttpException(
                $this->foreignKeyConstraintViolationMessage($entity, $context),
                $e,
            );
        }


        return null;
    }

    /** @return class-string */
    abstract protected function entityClass(): string;

    abstract protected function deleteEntity(object $entity, array $context): void;

    protected function foreignKeyConstraintViolationMessage(object $entity, array $context): string
    {
        return 'This resource cannot be deleted because it is still referenced by other resources.';
    }
}
