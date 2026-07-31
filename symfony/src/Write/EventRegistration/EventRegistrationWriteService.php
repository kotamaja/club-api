<?php

namespace App\Write\EventRegistration;

use App\Core\Event\Contract\Registration\EventRegistrationServiceInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Exception\ActiveEventRegistrationAlreadyExistsException;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Dto\EventRegistration\EventRegistrationCreateDto;
use App\Entity\ConnectionUser;
use App\Entity\Membership;
use App\Entity\Person;
use App\Repository\MembershipRepository;
use App\Repository\PersonRepository;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Exception\ReferencedResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

final class EventRegistrationWriteService implements EventRegistrationWriteServiceInterface
{
    public function __construct(private readonly EventRegistrationServiceInterface  $eventRegistrationService,
                                private readonly EventRegistrationPermissionChecker $permissionChecker,
                                private readonly OrganizationScopeGuard             $organizationScopeGuard,
                                private readonly PersonRepository                   $personRepository,
                                private readonly MembershipRepository               $membershipRepository,
                                private readonly EntityManagerInterface             $em)
    {
    }

    public function create(EventRegistrationCreateDto $input, Event $event, ConnectionUser $actor): EventRegistration
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($event);
        $this->permissionChecker->assertCanCreate($actor, $event);

        $person = $this->getPerson($input->personId);
        $membership = $this->getMembership($input->membershipId);

        if ($membership !== null && $membership->getPerson() !== $person) {
            throw new ReferencedResourceNotFoundException(
                'Membership not found for this person.',
                'membershipId',
            );
        }

        try {
            $registration = $this->eventRegistrationService->register(
                event: $event,
                person: $person,
                membership: $membership,
                now: new \DateTimeImmutable(),
            );
        } catch (ActiveEventRegistrationAlreadyExistsException $e) {
            throw new BusinessRuleViolationException(
                'An active registration already exists for this event and person.',
                'personId',
                $e,
            );
        } catch (EventRegistrationNotAllowedException $e) {
            throw new BusinessRuleViolationException(
                'Event does not accept registrations.',
                'eventId',
                $e,
            );
        }

        $registration->changeNote($input->note);

        $this->em->persist($registration);

        return $registration;
    }

    public function cancel(EventRegistration $registration, ConnectionUser $actor): EventRegistration
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($registration->getEvent());
        $this->permissionChecker->assertCanCancel($actor, $registration);

        $this->eventRegistrationService->cancel(
            registration: $registration,
            now: new \DateTimeImmutable(),
        );

        return $registration;
    }

    private function getPerson(?string $publicId): Person
    {
        if ($publicId === null || $publicId === '') {
            throw new ReferencedResourceNotFoundException('Person not found.', 'personId');
        }

        $person = $this->personRepository->findOneBy([
            'publicId' => $publicId,
        ]);

        if (!$person instanceof Person) {
            throw new ReferencedResourceNotFoundException('Person not found.', 'personId');
        }

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($person);

        return $person;
    }

    private function getMembership(?string $publicId): ?Membership
    {
        if ($publicId === null || $publicId === '') {
            return null;
        }

        $membership = $this->membershipRepository->findOneBy([
            'publicId' => $publicId,
        ]);

        if (!$membership instanceof Membership) {
            throw new ReferencedResourceNotFoundException('Membership not found.', 'membershipId');
        }

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($membership);

        return $membership;
    }
}
