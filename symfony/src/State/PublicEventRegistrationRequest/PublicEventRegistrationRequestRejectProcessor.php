<?php

namespace App\State\PublicEventRegistrationRequest;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Repository\PublicEventRegistrationRequestRepository;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestItemDto;
use App\Dto\EventRegistrationRequest\PublicEventRegistrationRequestRejectDto;
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
 * Handles back-office rejection of a public registration request.
 */
final readonly class PublicEventRegistrationRequestRejectProcessor implements ProcessorInterface
{
    use ProcessorActorTrait;

    public function __construct(private PublicEventRegistrationRequestRepository              $requestRepository,
                                private PublicEventRegistrationRequestReviewServiceInterface $reviewService,
                                private MapperRegistry                                      $mapperRegistry,
                                private EntityManagerInterface                              $em,
                                private Security                                            $security)
    {
    }

    /**
     * Rejects a pending public registration request.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PublicEventRegistrationRequestItemDto
    {
        if (!$data instanceof PublicEventRegistrationRequestRejectDto) {
            throw new \LogicException('Expected PublicEventRegistrationRequestRejectDto.');
        }

        $id = $uriVariables['id'] ?? null;

        if (!\is_string($id)) {
            throw new NotFoundHttpException('Public registration request not found.');
        }

        $publicRegistrationRequest = $this->requestRepository->findOneByPublicId($id);

        if (!$publicRegistrationRequest instanceof PublicEventRegistrationRequest) {
            throw new NotFoundHttpException('Public registration request not found.');
        }

        try {
            $request = $this->reviewService->reject(
                request: $publicRegistrationRequest,
                reason: $data->reason,
                actor: $this->getCurrentConnectionUser(),
            );

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
