<?php

namespace App\Community\Event\Policy\Registration;

use App\Core\Event\Contract\Capacity\EventCapacityPolicyInterface;
use App\Core\Event\Contract\Registration\PublicEventRegistrationRequestEligibilityPolicyInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Exception\EventCapacityExceededException;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Core\Event\Exception\PublicEventRegistrationRequestRejectedException;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Core\Event\Repository\PublicEventRegistrationRequestRepository;
use App\Repository\PersonRepository;

final readonly class CommunityPublicEventRegistrationRequestEligibilityPolicy implements PublicEventRegistrationRequestEligibilityPolicyInterface
{
    public function __construct(private EventCapacityPolicyInterface              $capacityPolicy,
                                private EventRegistrationRepository              $eventRegistrationRepository,
                                private PublicEventRegistrationRequestRepository $requestRepository,
                                private PersonRepository                         $personRepository)
    {
    }

    /**
     * Ensures that the public form can create a new pending request.
     *
     * These checks are intentionally strict because the endpoint is exposed to
     * unauthenticated users.
     */
    public function assertCanSubmit(Event $event, string $email, \DateTimeImmutable $now): void
    {
        $this->assertEventCanReceivePublicRequests($event, $now);

        if ($this->personRepository->hasQualifiedPersonForOrganizationAndEmail($event->getOrganization(), $email)) {
            throw new PublicEventRegistrationRequestRejectedException('Email already belongs to a qualified person.');
        }

        if ($this->requestRepository->hasPendingRequestForEventAndEmail($event, $email)) {
            throw new PublicEventRegistrationRequestRejectedException('A pending public registration request already exists for this event and email.');
        }

        if ($this->eventRegistrationRepository->hasActiveRegistrationForEventAndEmail($event, $email)) {
            throw new PublicEventRegistrationRequestRejectedException('An active registration already exists for this event and email.');
        }
    }

    /**
     * Ensures that a pending public request can still be accepted.
     *
     * The current request itself is already pending, so pending-request duplicate
     * checks must not be applied here.
     */
    public function assertCanAccept(PublicEventRegistrationRequest $request, \DateTimeImmutable $now): void
    {
        $event = $request->getEvent();
        $email = $request->getEmail();

        if (!$request->getStatus()->isPending()) {
            throw new PublicEventRegistrationRequestRejectedException('Only pending public registration requests can be accepted.');
        }

        $this->assertEventCanReceivePublicRequests($event, $now);

        if ($this->personRepository->hasQualifiedPersonForOrganizationAndEmail($event->getOrganization(), $email)) {
            throw new PublicEventRegistrationRequestRejectedException('Email already belongs to a qualified person.');
        }

        if ($this->eventRegistrationRepository->hasActiveRegistrationForEventAndEmail($event, $email)) {
            throw new PublicEventRegistrationRequestRejectedException('An active registration already exists for this event and email.');
        }
    }

    /**
     * Ensures that the event is still open for public registration requests.
     */
    private function assertEventCanReceivePublicRequests(Event $event, \DateTimeImmutable $now): void
    {
        if (!$event->getStatus()->acceptsRegistrations()) {
            throw new EventRegistrationNotAllowedException('Event does not accept registrations.');
        }

        if ($event->getClub() === null) {
            throw new EventRegistrationNotAllowedException('Public registration requests are only available for club events.');
        }

        if (!$event->isPublicRegistrationEnabled()) {
            throw new EventRegistrationNotAllowedException('Public registration is not enabled for this event.');
        }

        if (!$event->isWithinRegistrationWindow($now)) {
            throw new EventRegistrationNotAllowedException('Registration period is not open.');
        }

        if (!$this->capacityPolicy->hasAvailableCapacity($event) && !$event->isWaitlistEnabled()) {
            throw new EventCapacityExceededException();
        }
    }
}
