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
}
