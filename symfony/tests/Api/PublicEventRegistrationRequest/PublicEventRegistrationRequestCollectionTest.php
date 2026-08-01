<?php

namespace App\Tests\Api\PublicEventRegistrationRequest;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Enum\EventType;
use App\Factory\ClubFactory;
use App\Factory\EventFactory;
use App\Factory\OrganizationFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\Factories;

final class PublicEventRegistrationRequestCollectionTest extends ApiTestCase
{
    use Factories;

    /**
     * Ensures that back-office users can list public registration requests for an event.
     */
    public function testGetPublicRegistrationRequestsForEvent(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest();

        $firstRequest = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Alice',
            lastname: 'Durand',
            email: 'alice.durand@example.com',
            note: 'Première demande.',
            now: new \DateTimeImmutable('-2 hours'),
        );

        $secondRequest = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Bruno',
            lastname: 'Martin',
            email: 'bruno.martin@example.com',
            note: 'Deuxième demande.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($firstRequest);
        $em->persist($secondRequest);
        $em->flush();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId() . '/public-registration-requests');

        self::assertResponseStatusCodeSame(200);

        $data = $response->toArray(false);

        self::assertArrayHasKey('items', $data);
        self::assertCount(2, $data['items']);

        self::assertSame($firstRequest->getPublicId(), $data['items'][0]['id']);
        self::assertSame('Alice', $data['items'][0]['firstname']);
        self::assertSame('Durand', $data['items'][0]['lastname']);
        self::assertSame('alice.durand@example.com', $data['items'][0]['email']);
        self::assertSame('pending', $data['items'][0]['status']);
        self::assertSame($event->getPublicId(), $data['items'][0]['eventId']);
        self::assertSame('Initiation aviron', $data['items'][0]['eventTitle']);

        self::assertSame($secondRequest->getPublicId(), $data['items'][1]['id']);
        self::assertSame('Bruno', $data['items'][1]['firstname']);
        self::assertSame('Martin', $data['items'][1]['lastname']);
        self::assertSame('bruno.martin@example.com', $data['items'][1]['email']);
        self::assertSame('pending', $data['items'][1]['status']);
        self::assertSame($event->getPublicId(), $data['items'][1]['eventId']);
        self::assertSame('Initiation aviron', $data['items'][1]['eventTitle']);
    }


    /**
     * Ensures that the collection only returns requests for the selected event.
     */
    public function testGetPublicRegistrationRequestsForEventDoesNotReturnRequestsFromAnotherEvent(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest();
        $otherEvent = $this->createClubEventForPublicRegistrationRequest([
            'title' => 'Sortie découverte',
        ]);

        $matchingRequest = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Alice',
            lastname: 'Durand',
            email: 'alice.durand@example.com',
            note: 'Demande pour le bon événement.',
            now: new \DateTimeImmutable('-2 hours'),
        );

        $otherRequest = PublicEventRegistrationRequest::create(
            event: $otherEvent,
            firstname: 'Bruno',
            lastname: 'Martin',
            email: 'bruno.martin@example.com',
            note: 'Demande pour un autre événement.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($matchingRequest);
        $em->persist($otherRequest);
        $em->flush();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId() . '/public-registration-requests');

        self::assertResponseStatusCodeSame(200);

        $data = $response->toArray(false);

        self::assertArrayHasKey('items', $data);
        self::assertCount(1, $data['items']);
        self::assertSame($matchingRequest->getPublicId(), $data['items'][0]['id']);
        self::assertSame('Alice', $data['items'][0]['firstname']);
    }

    /**
     * Ensures that the collection does not expose requests from another organization.
     */
    public function testGetPublicRegistrationRequestsForEventFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::new()->create();

        $club = ClubFactory::new()
            ->forOrganization($otherOrganization)
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

        $request = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Alice',
            lastname: 'Durand',
            email: 'alice.durand@example.com',
            note: 'Demande d’une autre organisation.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($request);
        $em->flush();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId() . '/public-registration-requests');

        self::assertResponseStatusCodeSame(404);
    }


    /**
     * Creates a published club event that accepts public registration requests.
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
