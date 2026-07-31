<?php

namespace App\Community\Event\Service\Registration;

use App\Core\Event\Contract\Capacity\EventCapacityPolicyInterface;
use App\Core\Event\Contract\Registration\EventRegistrationEligibilityPolicyInterface;
use App\Core\Event\Contract\Registration\EventRegistrationServiceInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Exception\ActiveEventRegistrationAlreadyExistsException;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Entity\Membership;
use App\Entity\Person;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CommunityEventRegistrationService implements EventRegistrationServiceInterface
{
    public function __construct(private EventRegistrationEligibilityPolicyInterface $eligibilityPolicy,
                                private EventCapacityPolicyInterface                $capacityPolicy,
                                private EventRegistrationRepository                 $registrationRepository,
                                private EntityManagerInterface                      $entityManager)
    {
    }

    public function register(Event $event, Person $person, ?Membership $membership = null, ?DateTimeImmutable $now = null): EventRegistration
    {
        $now ??= new DateTimeImmutable();

        $this->eligibilityPolicy->assertCanRegister($event, $person, $now);

        if ($this->registrationRepository->hasActiveRegistrationForPerson($event, $person)) {
            throw new ActiveEventRegistrationAlreadyExistsException();
        }

        if ($this->capacityPolicy->hasAvailableCapacity($event)) {
            $registration = EventRegistration::register(event: $event, person: $person, membership: $membership, now: $now);
        } elseif ($event->isWaitlistEnabled()) {
            $registration = EventRegistration::waitlist(event: $event, person: $person, membership: $membership, now: $now);
        } else {
            $this->capacityPolicy->assertHasAvailableCapacity($event);

            throw new \LogicException('Unreachable code.');
        }

        $this->entityManager->persist($registration);

        return $registration;
    }

    public function cancel(EventRegistration $registration, ?DateTimeImmutable $now = null): EventRegistration
    {
        $now ??= new DateTimeImmutable();

        $registration->cancel($now);

        return $registration;
    }
}
