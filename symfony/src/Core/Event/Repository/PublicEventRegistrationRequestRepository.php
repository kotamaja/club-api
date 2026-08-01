<?php

namespace App\Core\Event\Repository;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\PublicEventRegistrationRequest;
use App\Core\Event\Enum\PublicEventRegistrationRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PublicEventRegistrationRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicEventRegistrationRequest::class);
    }

    /**
     * Checks whether a public request is already pending for the event and email.
     *
     * Only pending requests block a new public submission. Accepted and rejected
     * requests are already reviewed decisions.
     */
    public function hasPendingRequestForEventAndEmail(Event $event, string $email): bool
    {
        $result = $this->createQueryBuilder('request')
            ->select('1')
            ->andWhere('request.event = :event')
            ->andWhere('LOWER(request.email) = :email')
            ->andWhere('request.status = :status')
            ->setParameter('event', $event)
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setParameter('status', PublicEventRegistrationRequestStatus::Pending)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }

    /**
     * Finds a public registration request by its public identifier.
     */
    public function findOneByPublicId(string $publicId): ?PublicEventRegistrationRequest
    {
        return $this->findOneBy(['publicId' => $publicId]);
    }
}
