<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("dauguet.mathis@gmail.com");
        $user->setPassword('$2y$13$DT.alzkXv/gbmJguQaUdserT7UeEu3qxin.cUFqJH0C5RNlPMWxYC');
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $manager->flush();
    }
}
