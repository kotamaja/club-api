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
}
