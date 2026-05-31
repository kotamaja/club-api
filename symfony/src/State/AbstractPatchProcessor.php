<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Mapper\MapperRegistry;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use App\Write\Exception\ResourceConflictException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class AbstractPatchProcessor implements ProcessorInterface
{
    use OutputDtoResolverTrait;
    use ProcessorActorTrait;
    use PreviousDataEntityTrait;

    public function __construct(
        protected readonly MapperRegistry $mapperRegistry,
        protected readonly EntityManagerInterface $em,
        protected readonly Security $security,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): mixed {
        $this->assertInput($data);

        $entity = $this->getEntityFromContext($context, $this->entityClass());

        try {
            $this->patchEntity($data, $entity, $context);

            $this->em->flush();
        } catch (ReferencedResourceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ResourceConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        } catch (BusinessRuleViolationException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException(
                $this->uniqueConstraintViolationMessage($data, $entity, $context),
                $e,
            );
        }

        $outputDto = $this->resolveOutputDto($operation);

        return $this->mapperRegistry->map($entity, $outputDto);
    }

    abstract protected function assertInput(mixed $data): void;

    /**
     * @return class-string
     */
    abstract protected function entityClass(): string;

    abstract protected function patchEntity(
        mixed $data,
        object $entity,
        array $context,
    ): void;

    protected function uniqueConstraintViolationMessage(
        mixed $data,
        object $entity,
        array $context,
    ): string {
        return 'A resource with the same unique values already exists.';
    }
}
