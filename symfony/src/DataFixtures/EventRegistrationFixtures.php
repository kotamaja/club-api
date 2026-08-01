<?php

namespace App\DataFixtures;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Entity\Membership;
use App\Entity\Organization;
use App\Entity\Person;
use App\Factory\EventRegistrationFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventRegistrationFixtures extends Fixture implements DependentFixtureInterface
{
    private function create(
        ObjectManager $manager,
        string $reference,
        Event $event,
        Person $person,
        ?Membership $membership = null,
        bool $waitlisted = false,
        ?string $note = null,
        ?\DateTimeImmutable $requestedAt = null,
        ?\DateTimeImmutable $cancelledAt = null,
    ): EventRegistration {
        $requestedAt ??= new \DateTimeImmutable('2026-09-01T10:00:00+02:00');

        if ($waitlisted) {
            $registration = EventRegistration::waitlist(
                event: $event,
                person: $person,
                membership: $membership,
                now: $requestedAt,
            );
        } else {
            $registration = EventRegistration::register(
                event: $event,
                person: $person,
                membership: $membership,
                now: $requestedAt,
            );
        }

        $registration->changeNote($note);

        if ($cancelledAt !== null) {
            $registration->cancel($cancelledAt);
        }

        $manager->persist($registration);
        $this->addReference($reference, $registration);

        return $registration;
    }

    public function load(ObjectManager $manager): void
    {
        $organization1 = $this->getReference('Association Vaudoise Aviron', Organization::class);
        $organization2 = $this->getReference('Association Jurassienne Aviron', Organization::class);

        $eventInitiationLausanne = $this->getReference('event-initiation-aviron-lausanne', Event::class);
        $eventEntrainementLausanne = $this->getReference('event-entrainement-lausanne-sport', Event::class);
        $eventSortieLac = $this->getReference('event-sortie-lac-vaudoise', Event::class);
        $eventInitiationJura = $this->getReference('event-initiation-aviron-jura', Event::class);

        $person1 = $this->getReference('yves-a', Person::class);
        $person2 = $this->getReference('daniel-a', Person::class);
        $person3 = $this->getReference('marie-a', Person::class);
        $person4 = $this->getReference('serge-a', Person::class);
        $person5 = $this->getReference('monica-a', Person::class);

        $juraPerson1 = $this->getReference('yves-b', Person::class);
        $juraPerson2 = $this->getReference('olivier-b', Person::class);

        $this->create(
            manager: $manager,
            reference: 'event-registration-initiation-lausanne-1',
            event: $eventInitiationLausanne,
            person: $person1,
            note: 'Première participation, prévoir un accompagnement.',
            requestedAt: new \DateTimeImmutable('2026-08-01T09:15:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-initiation-lausanne-2',
            event: $eventInitiationLausanne,
            person: $person2,
            note: 'A déjà pratiqué en loisir.',
            requestedAt: new \DateTimeImmutable('2026-08-02T14:30:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-initiation-lausanne-waitlisted',
            event: $eventInitiationLausanne,
            person: $person3,
            waitlisted: true,
            note: 'Disponible uniquement le matin.',
            requestedAt: new \DateTimeImmutable('2026-08-03T08:45:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-initiation-lausanne-cancelled',
            event: $eventInitiationLausanne,
            person: $person4,
            note: 'Annulation pour raison personnelle.',
            requestedAt: new \DateTimeImmutable('2026-08-04T11:00:00+02:00'),
            cancelledAt: new \DateTimeImmutable('2026-08-10T16:20:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-entrainement-lausanne-1',
            event: $eventEntrainementLausanne,
            person: $person5,
            note: 'Participant régulier.',
            requestedAt: new \DateTimeImmutable('2026-09-01T18:00:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-sortie-lac-1',
            event: $eventSortieLac,
            person: $person1,
            note: 'Souhaite ramer avec le groupe débutant.',
            requestedAt: new \DateTimeImmutable('2026-09-05T10:30:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-initiation-jura-1',
            event: $eventInitiationJura,
            person: $juraPerson1,
            note: 'Inscription confirmée.',
            requestedAt: new \DateTimeImmutable('2026-08-16T09:00:00+02:00'),
        );

        $this->create(
            manager: $manager,
            reference: 'event-registration-initiation-jura-waitlisted',
            event: $eventInitiationJura,
            person: $juraPerson2,
            waitlisted: true,
            note: 'Liste d’attente.',
            requestedAt: new \DateTimeImmutable('2026-08-17T13:15:00+02:00'),
        );

        $manager->flush();

//        EventRegistrationFactory::new()
//            ->forEvent($eventInitiationLausanne)
//            ->many(10)
//            ->create();
//
//        EventRegistrationFactory::new()
//            ->forEvent($eventEntrainementLausanne)
//            ->many(5)
//            ->create();
//
//        EventRegistrationFactory::new()
//            ->forEvent($eventInitiationJura)
//            ->many(5)
//            ->create();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
            PersonFixtures::class,
            EventFixtures::class,
        ];
    }
}
