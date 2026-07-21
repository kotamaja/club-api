<?php

namespace App\DataFixtures;

use App\Core\Event\Entity\Event;
use App\Core\Event\Enum\EventType;
use App\Entity\Club;
use App\Entity\Organization;
use App\Factory\EventFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    private function create(
        ObjectManager $manager,
        string $reference,
        Organization $organization,
        string $title,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?Club $club = null,
        ?EventType $type = null,
        ?string $description = null,
        ?string $location = null,
        ?int $capacity = null,
        bool $waitlistEnabled = false,
        bool $publicRegistrationEnabled = false,
        ?\DateTimeImmutable $registrationStartsAt = null,
        ?\DateTimeImmutable $registrationEndsAt = null,
    ): Event {
        $event = Event::create(
            organization: $organization,
            title: $title,
            startsAt: $startsAt,
            endsAt: $endsAt,
        );

        if ($club !== null) {
            $event->attachToClub($club);
        }

        if ($type !== null) {
            $event->changeType($type);
        }

        $event->changeDescription($description);
        $event->changeLocation($location);
        $event->changeCapacity($capacity);

        if ($waitlistEnabled) {
            $event->enableWaitlist();
        }

        if ($publicRegistrationEnabled) {
            $event->enablePublicRegistration();
        }

        $event->changeRegistrationWindow($registrationStartsAt, $registrationEndsAt);

        $manager->persist($event);

        $this->addReference($reference, $event);

        return $event;
    }

    public function load(ObjectManager $manager): void
    {
        $organization1 = $this->getReference('Association Vaudoise Aviron', Organization::class);
        $organization2 = $this->getReference('Association Jurassienne Aviron', Organization::class);

        $rowingClubLausanne = $this->getReference('Rowing Club Lausanne', Club::class);
        $lausanneSportAviron = $this->getReference('Lausanne Sport Aviron', Club::class);
        $rowingClubDelemont = $this->getReference('Rowing Club Delemont', Club::class);

        $this->create(
            manager: $manager,
            reference: 'event-initiation-aviron-lausanne',
            organization: $organization1,
            title: 'Initiation aviron',
            startsAt: new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
            endsAt: new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
            club: $rowingClubLausanne,
            type: EventType::Course,
            description: 'Découverte de l’aviron pour les nouveaux participants.',
            location: 'Rowing Club Lausanne',
            capacity: 12,
            waitlistEnabled: true,
            publicRegistrationEnabled: true,
            registrationStartsAt: new \DateTimeImmutable('2026-08-01T08:00:00+02:00'),
            registrationEndsAt: new \DateTimeImmutable('2026-09-10T23:59:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-entrainement-lausanne-sport',
            organization: $organization1,
            title: 'Entraînement technique',
            startsAt: new \DateTimeImmutable('2026-09-16T18:00:00+02:00'),
            endsAt: new \DateTimeImmutable('2026-09-16T20:00:00+02:00'),
            club: $lausanneSportAviron,
            type: EventType::Training,
            description: 'Séance technique encadrée pour les membres.',
            location: 'Lausanne Sport Aviron',
            capacity: 16,
            waitlistEnabled: true,
        );

        $this->create(
            manager: $manager,
            reference: 'event-assemblee-generale-vaudoise',
            organization: $organization1,
            title: 'Assemblée générale',
            startsAt: new \DateTimeImmutable('2026-10-03T18:30:00+02:00'),
            endsAt: new \DateTimeImmutable('2026-10-03T21:00:00+02:00'),
            type: EventType::Meeting,
            description: 'Assemblée générale annuelle de l’association.',
            location: 'Salle principale',
            publicRegistrationEnabled: false,
        );

        $this->create(
            manager: $manager,
            reference: 'event-sortie-lac-vaudoise',
            organization: $organization1,
            title: 'Sortie sur le lac',
            startsAt: new \DateTimeImmutable('2026-09-20T08:30:00+02:00'),
            endsAt: new \DateTimeImmutable('2026-09-20T11:30:00+02:00'),
            type: EventType::Trip,
            description: 'Sortie encadrée pour les membres de l’association.',
            location: 'Port de Lausanne',
            capacity: 20,
            waitlistEnabled: true,
        );

        $this->create(
            manager: $manager,
            reference: 'event-initiation-aviron-jura',
            organization: $organization2,
            title: 'Initiation aviron Jura',
            startsAt: new \DateTimeImmutable('2026-09-19T09:00:00+02:00'),
            endsAt: new \DateTimeImmutable('2026-09-19T12:00:00+02:00'),
            club: $rowingClubDelemont,
            type: EventType::Course,
            description: 'Découverte de l’aviron pour les nouveaux participants.',
            location: 'Base nautique de Delémont',
            capacity: 10,
            waitlistEnabled: true,
            publicRegistrationEnabled: true,
            registrationStartsAt: new \DateTimeImmutable('2026-08-15T08:00:00+02:00'),
            registrationEndsAt: new \DateTimeImmutable('2026-09-17T23:59:00+02:00'),
        );

        $manager->flush();

        EventFactory::new()
            ->forOrganization($organization1)
            ->many(30)
            ->create();

        EventFactory::new()
            ->forClub($rowingClubLausanne)
            ->many(10)
            ->create();

        EventFactory::new()
            ->forOrganization($organization2)
            ->many(10)
            ->create();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
            ClubFixtures::class,
        ];
    }
}
