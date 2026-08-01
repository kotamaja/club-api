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

final class PublicEventRegistrationRequestRejectTest extends ApiTestCase
{
    use Factories;

    /**
     * Ensures that a pending public request can be rejected by a back-office user.
     */
    public function testRejectPublicRegistrationRequest(): void
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

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($request);
        $em->flush();

        $response = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/reject', [
            'reason' => 'Demande incomplète.',
        ]);

        self::assertResponseStatusCodeSame(200);

        $data = $response->toArray(false);

        self::assertSame($request->getPublicId(), $data['id']);
        self::assertSame('rejected', $data['status']);
        self::assertNotNull($data['reviewedAt']);
        self::assertSame($context->organizationUser->getPublicId(), $data['reviewedById']);
        self::assertSame('Demande incomplète.', $data['rejectionReason']);
        self::assertNull($data['createdPersonId']);
        self::assertNull($data['eventRegistrationId']);

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $refreshedRequest = $em
            ->getRepository(PublicEventRegistrationRequest::class)
            ->find($request->getId());

        self::assertNotNull($refreshedRequest);

        self::assertSame(PublicEventRegistrationRequestStatus::Rejected, $refreshedRequest->getStatus());
        self::assertNotNull($refreshedRequest->getReviewedAt());
        self::assertSame($context->organizationUser->getPublicId(), $refreshedRequest->getReviewedBy()?->getPublicId());
        self::assertSame('Demande incomplète.', $refreshedRequest->getRejectionReason());
        self::assertNull($refreshedRequest->getCreatedPerson());
        self::assertNull($refreshedRequest->getEventRegistration());
    }

    /**
     * Ensures that an accepted public request cannot be rejected later.
     */
    public function testRejectPublicRegistrationRequestRejectsAlreadyAcceptedRequest(): void
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

        $secondResponse = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/reject', [
            'reason' => 'Refus tardif.',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $secondResponse->toArray(false);

        self::assertSame('status: Only pending public registration requests can be rejected.', $data['detail']);
    }

    /**
     * Ensures that a rejected public request cannot be rejected again.
     */
    public function testRejectPublicRegistrationRequestRejectsAlreadyRejectedRequest(): void
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

        $firstResponse = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/reject', [
            'reason' => 'Demande incomplète.',
        ]);

        self::assertResponseStatusCodeSame(200);

        $secondResponse = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/reject', [
            'reason' => 'Deuxième refus.',
        ]);

        self::assertResponseStatusCodeSame(422);

        $data = $secondResponse->toArray(false);

        self::assertSame('status: Only pending public registration requests can be rejected.', $data['detail']);
    }

    /**
     * Ensures that a public request from another organization cannot be rejected.
     */
    public function testRejectPublicRegistrationRequestFromAnotherOrganizationReturnsNotFound(): void
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

        $response = $this->apiPost('/api/v1/public-registration-requests/' . $request->getPublicId() . '/reject', [
            'reason' => 'Demande incomplète.',
        ]);

        self::assertResponseStatusCodeSame(404);

        $data = $response->toArray(false);

        self::assertSame('id: Public registration request not found.', $data['detail']);
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
