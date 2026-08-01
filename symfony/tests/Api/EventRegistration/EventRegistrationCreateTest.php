<?php

namespace App\Tests\Api\EventRegistration;

use App\Core\Event\Enum\EventRegistrationStatus;
use App\Core\Event\Enum\EventStatus;
use App\Factory\ClubFactory;
use App\Factory\EventFactory;
use App\Factory\EventRegistrationFactory;
use App\Factory\MembershipFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class EventRegistrationCreateTest extends ApiTestCase
{
    public function testRegisterPersonToEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $event->publish();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
            ]);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame($event->getPublicId(), $data['eventId']);
        $this->assertSame($event->getTitle(), $data['eventTitle']);

        $this->assertSame($person->getPublicId(), $data['personId']);
        $this->assertSame('Jean', $data['personFirstname']);
        $this->assertSame('Dupont', $data['personLastname']);

        $this->assertNull($data['membershipId'] ?? null);

        $this->assertSame(EventRegistrationStatus::Registered->value, $data['status']);
        $this->assertNull($data['note'] ?? null);
    }

    public function testRegisterPersonToEventWithNote(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $event->publish();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Marie',
                'lastname' => 'Martin',
            ]);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
            'note' => '  À contacter avant la sortie.  ',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame($event->getPublicId(), $data['eventId']);
        $this->assertSame($person->getPublicId(), $data['personId']);
        $this->assertSame(EventRegistrationStatus::Registered->value, $data['status']);
        $this->assertSame('À contacter avant la sortie.', $data['note']);
    }

    public function testRegisterPersonToFullEventWithWaitlist(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'capacity' => 1,
                'waitlistEnabled' => true,
            ]);

        $event->publish();

        $registeredPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $waitlistedPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($registeredPerson)
            ->registered()
            ->create();

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $waitlistedPerson->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['eventId']);
        $this->assertSame($waitlistedPerson->getPublicId(), $data['personId']);
        $this->assertSame(EventRegistrationStatus::Waitlisted->value, $data['status']);
    }

    public function testRegisterRejectsDuplicateActiveRegistration(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $event->publish();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterRejectsDraftEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $this->assertSame(EventStatus::Draft, $event->getStatus());

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterRejectsPersonFromAnotherOrganization(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $event->publish();

        $otherOrganization = OrganizationFactory::createOne();

        $person = PersonFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRegisterPersonToEventWithMembership(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $targetClub = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $event = EventFactory::new()
            ->forClub($targetClub)
            ->create();

        $event->publish();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Paul',
                'lastname' => 'Durand',
            ]);

        $membership = MembershipFactory::new()
            ->forPerson($person)
            ->forClub($targetClub)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
                'endedAt' => null,
            ]);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
            'membershipId' => $membership->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['eventId']);
        $this->assertSame($person->getPublicId(), $data['personId']);
        $this->assertSame($membership->getPublicId(), $data['membershipId']);
        $this->assertSame(EventRegistrationStatus::Registered->value, $data['status']);
    }

    public function testRegisterRejectsFullEventWithoutWaitlist(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'capacity' => 1,
                'waitlistEnabled' => false,
            ]);

        $event->publish();

        $registeredPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $newPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($registeredPerson)
            ->registered()
            ->create();

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $newPerson->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
