<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Checks whether the email already belongs to a qualified person
     * in the given organization.
     *
     * Public registration requests must not be used for people that are already
     * known and managed by the organization.
     */
    public function hasQualifiedPersonForOrganizationAndEmail(Organization $organization, string $email): bool
    {
        $result = $this->createQueryBuilder('person')
            ->select('1')
            ->andWhere('person.organization = :organization')
            ->andWhere('LOWER(person.email) = :email')
            ->andWhere('person.createdFromPublicRegistration = false')
            ->setParameter('organization', $organization)
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}
