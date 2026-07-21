<?php

namespace App\Tests\Community\Event\Policy\Registration;

use App\Community\Event\Policy\Registration\CommunityEventRegistrationEligibilityPolicy;
use App\Core\Event\Entity\Event;
use App\Core\Event\Exception\EventRegistrationNotAllowedException;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class CommunityEventRegistrationEligibilityPolicyTest extends ApiTestCase
{
    private CommunityEventRegistrationEligibilityPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new CommunityEventRegistrationEligibilityPolicy();
    }

    public function testPublishedEventAcceptsRegistration(): void
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

        $now = new \DateTimeImmutable('2026-07-01 10:00:00');

        self::assertTrue($this->policy->canRegister($event, $person, $now));

        $this->policy->assertCanRegister($event, $person, $now);

        self::assertTrue(true);
    }

    public function testDraftEventRejectsRegistration(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $now = new \DateTimeImmutable('2026-07-01 10:00:00');

        self::assertFalse($this->policy->canRegister($event, $person, $now));

        $this->expectException(EventRegistrationNotAllowedException::class);

        $this->policy->assertCanRegister($event, $person, $now);
    }

    public function testRegistrationBeforeOpeningIsRejected(): void
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

        $now = new \DateTimeImmutable('2026-07-01 10:00:00');

        self::assertFalse($this->policy->canRegister($event, $person, $now));

        $this->expectException(EventRegistrationNotAllowedException::class);

        $this->policy->assertCanRegister($event, $person, $now);
    }

    public function testRegistrationAfterClosingIsRejected(): void
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

        $now = new \DateTimeImmutable('2026-07-06 10:00:00');

        self::assertFalse($this->policy->canRegister($event, $person, $now));

        $this->expectException(EventRegistrationNotAllowedException::class);

        $this->policy->assertCanRegister($event, $person, $now);
    }

    public function testRegistrationInsideWindowIsAccepted(): void
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
            startsAt: new \DateTimeImmutable('2026-07-01 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-05 18:00:00'),
        );

        $now = new \DateTimeImmutable('2026-07-03 10:00:00');

        self::assertTrue($this->policy->canRegister($event, $person, $now));

        $this->policy->assertCanRegister($event, $person, $now);

        self::assertTrue(true);
    }
}
