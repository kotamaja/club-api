<?php

namespace App\Core\Event\Repository;

use App\Core\Event\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Finds an event by its public identifier without using the current organization context.
     *
     * Public endpoints resolve the event directly from the public URL because they
     * do not receive an authenticated organization header.
     */
    public function findOneByPublicId(string $publicId): ?Event
    {
        return $this->findOneBy(['publicId' => $publicId]);
    }
}
