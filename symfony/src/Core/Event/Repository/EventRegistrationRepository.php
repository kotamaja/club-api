<?php

namespace App\Core\Event\Repository;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Enum\EventRegistrationStatus;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<EventRegistration>
 */
class EventRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }


    public function hasActiveRegistrationForPerson(Event $event, Person $person): bool
    {
        $count = (int) $this->createQueryBuilder('registration')
            ->select('COUNT(registration.id)')
            ->andWhere('registration.event = :event')
            ->andWhere('registration.person = :person')
            ->andWhere('registration.status IN (:activeStatuses)')
            ->setParameter('event', $event)
            ->setParameter('person', $person)
            ->setParameter('activeStatuses', [
                EventRegistrationStatus::Registered,
                EventRegistrationStatus::Waitlisted,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function findActiveRegistrationForPerson(Event $event, Person $person): ?EventRegistration
    {
        return $this->createQueryBuilder('registration')
            ->andWhere('registration.event = :event')
            ->andWhere('registration.person = :person')
            ->andWhere('registration.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('person', $person)
            ->setParameter('statuses', [
                EventRegistrationStatus::Registered,
                EventRegistrationStatus::Waitlisted,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByEventOrderedByRequestedAt(Event $event): array
    {
        return $this->createQueryBuilder('registration')
            ->andWhere('registration.event = :event')
            ->setParameter('event', $event)
            ->orderBy('registration.requestedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Checks whether the email already has an active registration for the event.
     *
     * Only registered and waitlisted registrations are considered active.
     * Cancelled registrations are historical records and do not block a new request.
     */
    public function hasActiveRegistrationForEventAndEmail(Event $event, string $email): bool
    {
        $result = $this->createQueryBuilder('registration')
            ->select('1')
            ->join('registration.person', 'person')
            ->andWhere('registration.event = :event')
            ->andWhere('LOWER(person.email) = :email')
            ->andWhere('registration.status IN (:statuses)')
            ->setParameter('event', $event)
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setParameter('statuses', [
                EventRegistrationStatus::Registered,
                EventRegistrationStatus::Waitlisted,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}
