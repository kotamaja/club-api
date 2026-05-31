<?php

namespace App\DataFixtures;

use App\Entity\ConnectionUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ConnectionUserFixtures extends Fixture
{


    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {




        $user = new ConnectionUser("daniel@test.com");


        $passwordHash = $this->passwordHasher->hashPassword(
            $user,
            "welcome"
        );
        $user->activate($passwordHash);

        $this->addReference("Daniel", $user);
        $manager->persist($user);

        $user = new ConnectionUser("yves@test.com");

        $passwordHash = $this->passwordHasher->hashPassword(
            $user,
            "welcome"
        );
        $user->activate($passwordHash);
        $this->addReference("Yves", $user);
        $manager->persist($user);

        $manager->flush();
    }


}
