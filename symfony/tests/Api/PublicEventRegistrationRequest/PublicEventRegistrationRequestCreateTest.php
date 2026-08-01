<?php

namespace App\Tests\Api\PublicEventRegistrationRequest;

use App\Core\Event\Entity\Event;
use App\Core\Event\Enum\EventType;
use App\Factory\ClubFactory;
use App\Factory\EventFactory;
use App\Factory\EventRegistrationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\Factories;

final class PublicEventRegistrationRequestCreateTest extends ApiTestCase
{
    use Factories;

    /**
     * Ensures that unauthenticated users can submit a public request for an eligible club event.
     */
    public function testSubmitPublicRegistrationRequest(): void
    {
        $context = $this->getAuthenticatedOrganizationContext();
        $club = ClubFactory::new()
            ->forOrganization($context->organization)
            ->create();

        $event = EventFactory::new()
            ->forClub($club)
            ->create([
                'title' => 'Initiation aviron',
                'type' => EventType::Course,
                'startsAt' => new \DateTimeImmutable('+10 days'),
                'endsAt' => new \DateTimeImmutable('+10 days +2 hours'),
                'capacity' => 10,
                'waitlistEnabled' => false,
                'publicRegistrationEnabled' => true,
                'registrationStartsAt' => new \DateTimeImmutable('-1 day'),
                'registrationEndsAt' => new \DateTimeImmutable('+5 days'),
            ]);

        $event->publish();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'Alice.Durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $response->toArray(false);

        self::assertArrayHasValidUlid($data, 'id');
        self::assertSame($event->getPublicId(), $data['eventId']);
        self::assertSame('Initiation aviron', $data['eventTitle']);
        self::assertSame($club->getPublicId(), $data['clubId']);
        self::assertSame($club->getName(), $data['clubName']);
        self::assertSame('Alice', $data['firstname']);
        self::assertSame('Durand', $data['lastname']);
        self::assertSame('alice.durand@example.com', $data['email']);
        self::assertSame('Je souhaite participer à cette initiation.', $data['note']);
        self::assertSame('pending', $data['status']);
        self::assertNull($data['reviewedAt']);
        self::assertNull($data['reviewedById']);
        self::assertNull($data['reviewedByDisplayName']);
        self::assertNull($data['createdPersonId']);
        self::assertNull($data['eventRegistrationId']);
        self::assertNull($data['rejectionReason']);
    }


    /**
     * Ensures that basic bot submissions are rejected when the honeypot field is filled.
     */
    public function testSubmitPublicRegistrationRequestRejectsFilledHoneypot(): void
    {
        $context = $this->getAuthenticatedOrganizationContext();
        $club = ClubFactory::new()
            ->forOrganization($context->organization)
            ->create();

        $event = EventFactory::new()
            ->forClub($club)
            ->create([
                'title' => 'Initiation aviron',
                'type' => EventType::Course,
                'startsAt' => new \DateTimeImmutable('+10 days'),
                'endsAt' => new \DateTimeImmutable('+10 days +2 hours'),
                'capacity' => 10,
                'waitlistEnabled' => false,
                'publicRegistrationEnabled' => true,
                'registrationStartsAt' => new \DateTimeImmutable('-1 day'),
                'registrationEndsAt' => new \DateTimeImmutable('+5 days'),
            ]);

        $event->publish();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => 'https://spam.example.com',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('Registration request cannot be submitted.', $data['detail']);
    }

    /**
     * Ensures that public requests cannot be submitted when public registration is disabled.
     */
    public function testSubmitPublicRegistrationRequestRejectsDisabledPublicRegistration(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest([
            'publicRegistrationEnabled' => false,
        ]);

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('eventId: Public registration requests are not open for this event.', $data['detail']);
    }


    /**
     * Ensures that public requests cannot be submitted for draft events.
     */
    public function testSubmitPublicRegistrationRequestRejectsDraftEvent(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest(published: false);

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('eventId: Public registration requests are not open for this event.', $data['detail']);
    }

    /**
     * Ensures that public requests cannot be submitted for organization-level events.
     */
    public function testSubmitPublicRegistrationRequestRejectsEventWithoutClub(): void
    {
        $context = $this->getAuthenticatedOrganizationContext();

        $event = EventFactory::new()
            ->forOrganization($context->organization)
            ->create([
                'title' => 'Assemblée générale',
                'type' => EventType::Meeting,
                'startsAt' => new \DateTimeImmutable('+10 days'),
                'endsAt' => new \DateTimeImmutable('+10 days +2 hours'),
                'capacity' => 10,
                'waitlistEnabled' => false,
                'publicRegistrationEnabled' => true,
                'registrationStartsAt' => new \DateTimeImmutable('-1 day'),
                'registrationEndsAt' => new \DateTimeImmutable('+5 days'),
            ]);

        $event->publish();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette assemblée.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('eventId: Public registration requests are not open for this event.', $data['detail']);
    }


    /**
     * Ensures that public requests cannot be submitted before the registration window opens.
     */
    public function testSubmitPublicRegistrationRequestRejectsBeforeRegistrationWindow(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest([
            'registrationStartsAt' => new \DateTimeImmutable('+1 day'),
            'registrationEndsAt' => new \DateTimeImmutable('+5 days'),
        ]);

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('eventId: Public registration requests are not open for this event.', $data['detail']);
    }

    /**
     * Ensures that public requests cannot be submitted after the registration window closes.
     */
    public function testSubmitPublicRegistrationRequestRejectsAfterRegistrationWindow(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest([
            'registrationStartsAt' => new \DateTimeImmutable('-5 days'),
            'registrationEndsAt' => new \DateTimeImmutable('-1 day'),
        ]);

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('eventId: Public registration requests are not open for this event.', $data['detail']);
    }

    /**
     * Ensures that public requests cannot be submitted when the event is full and waitlist is disabled.
     */
    public function testSubmitPublicRegistrationRequestRejectsFullEventWithoutWaitlist(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest([
            'capacity' => 1,
            'waitlistEnabled' => false,
        ]);

        $person = PersonFactory::new()
            ->forOrganization($event->getOrganization())
            ->create([
                'firstname' => 'Bob',
                'lastname' => 'Martin',
                'email' => 'bob.martin@example.com',
            ]);

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('eventId: Public registration requests are not open for this event.', $data['detail']);
    }

    /**
     * Ensures that public requests can be submitted when the event is full but waitlist is enabled.
     */
    public function testSubmitPublicRegistrationRequestAllowsFullEventWithWaitlist(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest([
            'capacity' => 1,
            'waitlistEnabled' => true,
        ]);

        $person = PersonFactory::new()
            ->forOrganization($event->getOrganization())
            ->create([
                'firstname' => 'Bob',
                'lastname' => 'Martin',
                'email' => 'bob.martin@example.com',
            ]);

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Je souhaite être ajoutée à la liste d’attente.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $response->toArray(false);

        self::assertSame('pending', $data['status']);
        self::assertSame('alice.durand@example.com', $data['email']);
    }

    /**
     * Ensures that public requests are rejected when the email belongs to a qualified person.
     */
    public function testSubmitPublicRegistrationRequestRejectsQualifiedPersonEmail(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest();

        PersonFactory::new()
            ->forOrganization($event->getOrganization())
            ->create([
                'firstname' => 'Alice',
                'lastname' => 'Durand',
                'email' => 'alice.durand@example.com',
                'createdFromPublicRegistration' => false,
            ]);

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'Alice.Durand@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('email: Registration request cannot be submitted for this email.', $data['detail']);
    }

    /**
     * Ensures that a second public request is rejected while a request is already pending.
     */
    public function testSubmitPublicRegistrationRequestRejectsDuplicatePendingRequestEmail(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest();

        $firstResponse = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'alice.durand@example.com',
            'note' => 'Première demande.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(201);

        $secondResponse = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'ALICE.DURAND@example.com',
            'note' => 'Deuxième demande.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $secondResponse->toArray(false);

        self::assertSame('email: Registration request cannot be submitted for this email.', $data['detail']);
    }

    /**
     * Ensures that public requests are rejected when the email already has an active event registration.
     */
    public function testSubmitPublicRegistrationRequestRejectsActiveRegistrationEmail(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest();

        $person = PersonFactory::new()
            ->forOrganization($event->getOrganization())
            ->create([
                'firstname' => 'Alice',
                'lastname' => 'Durand',
                'email' => 'alice.durand@example.com',
            ]);

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPublicPost('/api/v1/public/events/' . $event->getPublicId() . '/registration-requests', [
            'firstname' => 'Alice',
            'lastname' => 'Durand',
            'email' => 'ALICE.DURAND@example.com',
            'note' => 'Je souhaite participer à cette initiation.',
            'homepage' => '',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('email: Registration request cannot be submitted for this email.', $data['detail']);
    }


    /**
     * Creates a club event for public registration request tests.
     */
    private function createClubEventForPublicRegistrationRequest(array $attributes = [], bool $published = true): Event
    {
        $context = $this->getAuthenticatedOrganizationContext();
        $club = ClubFactory::new()
            ->forOrganization($context->organization)
            ->create();

        $event = EventFactory::new()
            ->forClub($club)
            ->create([
                'title' => 'Initiation aviron',
                'type' => EventType::Course,
                'startsAt' => new \DateTimeImmutable('+10 days'),
                'endsAt' => new \DateTimeImmutable('+10 days +2 hours'),
                'capacity' => 10,
                'waitlistEnabled' => false,
                'publicRegistrationEnabled' => true,
                'registrationStartsAt' => new \DateTimeImmutable('-1 day'),
                'registrationEndsAt' => new \DateTimeImmutable('+5 days'),
                ...$attributes,
            ]);

        if ($published) {
            $event->publish();
        }

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        return $event;
    }

}
