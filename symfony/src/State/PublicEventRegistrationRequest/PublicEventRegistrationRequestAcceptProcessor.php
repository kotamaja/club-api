<?php

namespace App\State\PublicEventRegistrationRequest;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestItemDto;
use App\Mapper\MapperRegistry;
use App\State\ProcessorActorTrait;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use App\Write\Exception\ResourceConflictException;
use App\Write\PublicEventRegistrationRequest\PublicEventRegistrationRequestReviewServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Handles back-office acceptance of a public registration request.
 */
final readonly class PublicEventRegistrationRequestAcceptProcessor implements ProcessorInterface
{
    use ProcessorActorTrait;

    public function __construct(private PublicEventRegistrationRequestReviewServiceInterface $reviewService,
                                private MapperRegistry                                       $mapperRegistry,
                                private EntityManagerInterface                               $em,
                                private Security                                             $security)
    {

    }

    /**
     * Accepts a pending public registration request.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PublicEventRegistrationRequestItemDto
    {
        if (!$data instanceof PublicEventRegistrationRequest) {
            throw new NotFoundHttpException('Public registration request not found.');
        }

        try {
            $request = $this->reviewService->accept($data, $this->getCurrentConnectionUser());

            $this->em->flush();
        } catch (ReferencedResourceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ResourceConflictException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        } catch (BusinessRuleViolationException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $this->mapperRegistry->map($request, PublicEventRegistrationRequestItemDto::class);
    }
}
