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

final class PublicEventRegistrationRequestItemTest extends ApiTestCase
{
    use Factories;

    /**
     * Ensures that a back-office user can read a visible public registration request.
     */
    public function testGetPublicRegistrationRequest(): void
    {
        $event = $this->createClubEventForPublicRegistrationRequest();

        $request = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Alice',
            lastname: 'Durand',
            email: 'alice.durand@example.com',
            note: 'Je souhaite participer à cette initiation.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($request);
        $em->flush();

        $response = $this->apiGet('/api/v1/public-registration-requests/' . $request->getPublicId());

        self::assertResponseStatusCodeSame(200);

        $data = $response->toArray(false);

        self::assertSame($request->getPublicId(), $data['id']);
        self::assertSame($event->getPublicId(), $data['eventId']);
        self::assertSame('Initiation aviron', $data['eventTitle']);
        self::assertSame('Alice', $data['firstname']);
        self::assertSame('Durand', $data['lastname']);
        self::assertSame('alice.durand@example.com', $data['email']);
        self::assertSame('Je souhaite participer à cette initiation.', $data['note']);
        self::assertSame('pending', $data['status']);
        self::assertNotNull($data['requestedAt']);

        self::assertArrayNotHasKey('reviewedAt', $data);
        self::assertArrayNotHasKey('reviewedById', $data);
        self::assertArrayNotHasKey('reviewedByDisplayName', $data);
        self::assertArrayNotHasKey('createdPersonId', $data);
        self::assertArrayNotHasKey('eventRegistrationId', $data);
        self::assertArrayNotHasKey('rejectionReason', $data);
    }

    /**
     * Ensures that a public request from another organization is not visible.
     */
    public function testGetPublicRegistrationRequestFromAnotherOrganizationReturnsNotFound(): void
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

        $response = $this->apiGet('/api/v1/public-registration-requests/' . $request->getPublicId());

        self::assertResponseStatusCodeSame(404);

        $data = $response->toArray(false);

        self::assertSame('Not Found', $data['detail']);
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
