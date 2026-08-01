<?php

namespace App\Write\PublicEventRegistrationRequest;

use App\Core\Event\Contract\Registration\EventRegistrationServiceInterface;
use App\Core\Event\Contract\Registration\PublicEventRegistrationRequestEligibilityPolicyInterface;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Exception\ActiveEventRegistrationAlreadyExistsException;
use App\Core\Event\Exception\EventCapacityExceededException;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Core\Event\Exception\PublicEventRegistrationRequestRejectedException;
use App\Entity\ConnectionUser;
use App\Entity\OrganizationUser;
use App\Entity\Person;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PublicEventRegistrationRequestReviewService implements PublicEventRegistrationRequestReviewServiceInterface
{
    public function __construct(private CurrentOrganizationContext                               $currentOrganizationContext,
                                private PublicEventRegistrationRequestEligibilityPolicyInterface $eligibilityPolicy,
                                private EventRegistrationServiceInterface                        $eventRegistrationService,
                                private EntityManagerInterface                                   $em)
    {
    }

    /**
     * Accepts a pending public request after rechecking the event and email constraints.
     *
     * Public requests may wait for some time before review, so all critical rules
     * are checked again before creating a Person and an EventRegistration.
     */
    public function accept(PublicEventRegistrationRequest $request, ConnectionUser $actor): PublicEventRegistrationRequest
    {
        $now = new \DateTimeImmutable();
        $reviewer = $this->getReviewer($actor);
        $event = $request->getEvent();
        $email = $request->getEmail();

        $this->assertRequestBelongsToCurrentOrganization($request, $reviewer);

        if (!$request->getStatus()->isPending()) {
            throw new BusinessRuleViolationException(
                'Only pending public registration requests can be accepted.',
                'status',
            );
        }

        try {
            $this->eligibilityPolicy->assertCanAccept($request, $now);
        } catch (EventCapacityExceededException $e) {
            throw new BusinessRuleViolationException(
                'Public registration request can no longer be accepted for this event.',
                'eventId',
                $e,
            );
        } catch (EventRegistrationNotAllowedException $e) {
            throw new BusinessRuleViolationException(
                'Public registration request can no longer be accepted for this event.',
                'eventId',
                $e,
            );
        } catch (PublicEventRegistrationRequestRejectedException $e) {
            throw new BusinessRuleViolationException(
                'Public registration request can no longer be accepted for this email.',
                'email',
                $e,
            );
        }

        $person = Person::create(
            firstname: $request->getFirstname(),
            lastname: $request->getLastname(),
            email: $email,
            organization: $request->getOrganization(),
        );

        $person->markAsCreatedFromPublicRegistration();

        $this->em->persist($person);

        // The registration service checks existing registrations with a Doctrine query.
        // The newly created person must therefore have a database identifier before it
        // can safely be used as a query parameter.
        $this->em->flush();

        try {
            $registration = $this->eventRegistrationService->register(
                event: $event,
                person: $person,
                membership: null,
                now: $now,
            );
        } catch (ActiveEventRegistrationAlreadyExistsException $e) {
            throw new BusinessRuleViolationException(
                'Public registration request can no longer be accepted for this email.',
                'email',
                $e,
            );
        } catch (EventRegistrationNotAllowedException $e) {
            throw new BusinessRuleViolationException(
                'Public registration request can no longer be accepted for this event.',
                'eventId',
                $e,
            );
        } catch (EventCapacityExceededException $e) {
            throw new BusinessRuleViolationException(
                'Public registration request can no longer be accepted for this event.',
                'eventId',
                $e,
            );
        }

        $registration->changeNote($request->getNote());
        $request->accept($person, $registration, $reviewer, $now);

        return $request;
    }

    /**
     * Rejects a pending public request in the current organization.
     */
    public function reject(PublicEventRegistrationRequest $request, ?string $reason, ConnectionUser $actor): PublicEventRegistrationRequest
    {
        $now = new \DateTimeImmutable();
        $reviewer = $this->getReviewer($actor);

        $this->assertRequestBelongsToCurrentOrganization($request, $reviewer);

        if (!$request->getStatus()->isPending()) {
            throw new BusinessRuleViolationException(
                'Only pending public registration requests can be rejected.',
                'status',
            );
        }

        $request->reject($reason, $reviewer, $now);

        return $request;
    }

    /**
     * Resolves the organization-scoped reviewer for the current authenticated actor.
     */
    private function getReviewer(ConnectionUser $actor): OrganizationUser
    {
        $organizationUser = $this->currentOrganizationContext->getOrganizationUser();

        if (!$organizationUser instanceof OrganizationUser) {
            throw new ReferencedResourceNotFoundException(
                'Current organization user not found.',
                'organizationUser',
            );
        }

        if ($organizationUser->getConnectionUser() !== $actor) {
            throw new ReferencedResourceNotFoundException(
                'Current organization user not found.',
                'organizationUser',
            );
        }

        return $organizationUser;
    }

    /**
     * Ensures that the reviewed request belongs to the current organization.
     */
    private function assertRequestBelongsToCurrentOrganization(PublicEventRegistrationRequest $request, OrganizationUser $reviewer): void
    {
        if ($request->getOrganization() !== $reviewer->getOrganization()) {
            throw new ReferencedResourceNotFoundException(
                'Public registration request not found.',
                'id',
            );
        }
    }
}
