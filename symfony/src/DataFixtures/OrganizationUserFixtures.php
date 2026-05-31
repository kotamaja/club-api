<?php

namespace App\DataFixtures;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\OrganizationUser;
use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationUserFixtures  extends Fixture implements DependentFixtureInterface
{


    public function load(ObjectManager $manager): void
    {
        $connectionUser = $this->getReference("connected user", ConnectionUser::class);
        $person1 = $this->getReference("yves-a", Person::class);

        $organization1 = $this->getReference("Association Vaudoise Aviron", Organization::class);

        $user1 = new  OrganizationUser( $connectionUser,$organization1, [], $person1  );
        $organization2 = $this->getReference("Association Jurassienne Aviron", Organization::class);
        $person2 = $this->getReference("yves-b", Person::class);


        $user2 = new  OrganizationUser( $connectionUser,$organization2, [], $person2  );

        $manager->persist($user1);
        $manager->persist($user2);

        $manager->flush();

    }




    public function getDependencies(): array
    {
        return [
            PersonFixtures::class,
            OrganizationFixtures::class,
            ConnectionUserFixtures::class,
        ];
    }

}
