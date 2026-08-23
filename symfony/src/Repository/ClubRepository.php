<?php

namespace App\Repository;

use App\Entity\Club;
use App\Entity\Organization;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    /**
     * @return list<Club>
     */
    public function findAccessibleForPerson(Organization $organization, Person $person): array
    {
        return $this->createQueryBuilder('club')
            ->innerJoin('club.memberships', 'membership')
            ->andWhere('club.organization = :organization')
            ->andWhere('membership.organization = :organization')
            ->andWhere('membership.person = :person')
            ->andWhere('membership.endedAt IS NULL')
            ->setParameter('organization', $organization)
            ->setParameter('person', $person)
            ->orderBy('club.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
