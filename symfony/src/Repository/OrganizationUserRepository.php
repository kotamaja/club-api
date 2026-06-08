<?php

namespace App\Repository;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\OrganizationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganizationUser>
 */
class OrganizationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganizationUser::class);
    }

    public function findOneActiveByConnectionUserAndOrganization(ConnectionUser $connectionUser, Organization $organization): ?OrganizationUser
    {
        return $this->findOneBy([
            'connectionUser' => $connectionUser,
            'organization' => $organization,
            'enabled' => true,
        ]);
    }

    /**
     * @return list<OrganizationUser>
     */
    public function findActiveByConnectionUser(ConnectionUser $connectionUser): array
    {
        return $this->createQueryBuilder('ou')
            ->addSelect('o')
            ->leftJoin('ou.organization', 'o')
            ->leftJoin('ou.person', 'p')
            ->addSelect('p')
            ->andWhere('ou.connectionUser = :connectionUser')
            ->andWhere('ou.enabled = true')
            ->andWhere('o.enabled = true')
            ->setParameter('connectionUser', $connectionUser)
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
