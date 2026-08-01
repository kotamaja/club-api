<?php

namespace App\Tests\Api\PublicEventRegistrationRequest;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Enum\EventStatus;
use App\Core\Event\Enum\EventType;
use App\Core\Event\Enum\PublicEventRegistrationRequestStatus;
use App\Factory\ClubFactory;
use App\Factory\EventFactory;
use App\Factory\OrganizationFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\Factories;

final class PublicEventRegistrationRequestAcceptTest extends ApiTestCase
{
    use Factories;

    /**
     * Ensures that a pending public request can be accepted by a back-office user.
     */
    public function testAcceptPublicRegistrationRequest(): void
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

        $request = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Alice',
            lastname: 'Durand',
            email: 'alice.durand@example.com',
            note: 'Je souhaite participer à cette initiation.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->persist($request);

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiPost(
            '/api/v1/public-registration-requests/' . $request->getPublicId() . '/accept',
            []
        );

        self::assertResponseStatusCodeSame(200);

        $data = $response->toArray(false);

        self::assertSame($request->getPublicId(), $data['id']);
        self::assertSame('accepted', $data['status']);
        self::assertNotNull($data['reviewedAt']);
        self::assertSame($context->organizationUser->getPublicId(), $data['reviewedById']);
        self::assertNotNull($data['createdPersonId']);
        self::assertNotNull($data['eventRegistrationId']);
        self::assertNull($data['rejectionReason']);
    }

    /**
     * Ensures that a rejected public request cannot be accepted later.
     */
    public function testAcceptPublicRegistrationRequestRejectsAlreadyRejectedRequest(): void
    {
        $context = $this->getAuthenticatedOrganizationContext();
        $event = $this->createClubEventForPublicRegistrationRequest();

        $request = PublicEventRegistrationRequest::create(
            event: $event,
            firstname: 'Alice',
            lastname: 'Durand',
            email: 'alice.durand@example.com',
            note: 'Je souhaite participer à cette initiation.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        $request->reject(
            reason: 'Demande incomplète.',
            reviewedBy: $context->organizationUser,
            now: new \DateTimeImmutable('-30 minutes'),
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($request);
        $em->flush();

        $response = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/accept', []);

        self::assertResponseStatusCodeSame(422);

        $data = $response->toArray(false);

        self::assertSame('status: Only pending public registration requests can be accepted.', $data['detail']);
    }


    /**
     * Ensures that an accepted public request cannot be accepted again.
     */
    public function testAcceptPublicRegistrationRequestRejectsAlreadyAcceptedRequest(): void
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

        $firstResponse = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/accept', []);

        self::assertResponseStatusCodeSame(200);

        $secondResponse = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/accept',[]);

        self::assertResponseStatusCodeSame(422);

        $data = $secondResponse->toArray(false);

        self::assertSame('status: Only pending public registration requests can be accepted.', $data['detail']);
    }


    /**
     * Ensures that a public request from another organization cannot be accepted.
     */
    public function testAcceptPublicRegistrationRequestFromAnotherOrganizationReturnsNotFound(): void
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
            note: 'Je souhaite participer à cette initiation.',
            now: new \DateTimeImmutable('-1 hour'),
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($request);
        $em->flush();

        $response = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/accept', []);

        self::assertResponseStatusCodeSame(404);

        $data = $response->toArray(false);

        self::assertSame('Public registration request not found.', $data['detail']);
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
