<?php

namespace App\Tests\Community\Event\Service\Registration;

use App\Community\Event\Service\Registration\CommunityEventRegistrationService;
use App\Core\Event\Contract\Registration\EventRegistrationServiceInterface;
use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Enum\EventRegistrationStatus;
use App\Core\Event\Exception\ActiveEventRegistrationAlreadyExistsException;
use App\Core\Event\Exception\EventCapacityExceededException;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Core\Event\Repository\EventRegistrationRepository;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class CommunityEventRegistrationServiceTest extends ApiTestCase
{
    private EventRegistrationServiceInterface $service;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var EventRegistrationServiceInterface $service */
        $service = static::getContainer()->get(EventRegistrationServiceInterface::class);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->service = $service;
        $this->entityManager = $entityManager;
    }

    public function testRegisterCreatesRegisteredRegistrationWhenCapacityIsAvailable(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();
        $event->changeCapacity(10);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $now = new \DateTimeImmutable('2026-07-01 10:00:00');

        $registration = $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: $now,
        );

        self::assertSame(EventRegistrationStatus::Registered, $registration->getStatus());
        self::assertSame($event, $registration->getEvent());
        self::assertSame($person, $registration->getPerson());
        self::assertNull($registration->getMembership());
        self::assertSame($now, $registration->getRequestedAt());
        self::assertSame($now, $registration->getConfirmedAt());
        self::assertNull($registration->getCancelledAt());

        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var EventRegistrationRepository $repository */
        $repository = static::getContainer()->get(EventRegistrationRepository::class);

        $savedRegistration = $repository->findOneBy([
            'publicId' => $registration->getPublicId(),
        ]);

        self::assertNotNull($savedRegistration);
        self::assertSame(EventRegistrationStatus::Registered, $savedRegistration->getStatus());
    }

    public function testRegisterCreatesWaitlistedRegistrationWhenEventIsFullAndWaitlistIsEnabled(): void
    {
        $organization = OrganizationFactory::createOne();

        $firstPerson = PersonFactory::new()->forOrganization($organization)->create();
        $secondPerson = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();
        $event->changeCapacity(1);
        $event->enableWaitlist();

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $firstRegistration = $this->service->register(
            event: $event,
            person: $firstPerson,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        self::assertSame(EventRegistrationStatus::Registered, $firstRegistration->getStatus());

        $this->entityManager->flush();

        $secondRegistration = $this->service->register(
            event: $event,
            person: $secondPerson,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 11:00:00'),
        );

        self::assertSame(EventRegistrationStatus::Waitlisted, $secondRegistration->getStatus());
        self::assertNull($secondRegistration->getConfirmedAt());
        self::assertNull($secondRegistration->getCancelledAt());
    }

    public function testRegisterRejectsWhenEventIsFullAndWaitlistIsDisabled(): void
    {
        $organization = OrganizationFactory::createOne();

        $firstPerson = PersonFactory::new()->forOrganization($organization)->create();
        $secondPerson = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();
        $event->changeCapacity(1);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->service->register(
            event: $event,
            person: $firstPerson,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $this->entityManager->flush();

        $this->expectException(EventCapacityExceededException::class);

        $this->service->register(
            event: $event,
            person: $secondPerson,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 11:00:00'),
        );
    }

    public function testRegisterRejectsDraftEvent(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->expectException(EventRegistrationNotAllowedException::class);

        $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );
    }

    public function testRegisterRejectsWhenRegistrationWindowIsNotOpenYet(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();
        $event->changeRegistrationWindow(
            startsAt: new \DateTimeImmutable('2026-07-05 09:00:00'),
            endsAt: null,
        );

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->expectException(EventRegistrationNotAllowedException::class);

        $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );
    }

    public function testRegisterRejectsWhenRegistrationWindowIsClosed(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();
        $event->changeRegistrationWindow(
            startsAt: null,
            endsAt: new \DateTimeImmutable('2026-07-05 18:00:00'),
        );

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->expectException(EventRegistrationNotAllowedException::class);

        $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-06 10:00:00'),
        );
    }

    public function testRegisterRejectsWhenActiveRegistrationAlreadyExists(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $this->entityManager->flush();

        $this->expectException(ActiveEventRegistrationAlreadyExistsException::class);

        $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 11:00:00'),
        );
    }

    public function testRegisterAllowsNewRegistrationWhenPreviousRegistrationIsCancelled(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->publish();

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $firstRegistration = $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $firstRegistration->cancel(new \DateTimeImmutable('2026-07-01 12:00:00'));

        $this->entityManager->flush();

        $secondRegistration = $this->service->register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-02 10:00:00'),
        );

        self::assertNotSame($firstRegistration, $secondRegistration);
        self::assertSame(EventRegistrationStatus::Registered, $secondRegistration->getStatus());
        self::assertSame(EventRegistrationStatus::Cancelled, $firstRegistration->getStatus());
    }
}
