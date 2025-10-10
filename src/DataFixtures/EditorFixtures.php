<?php

namespace App\DataFixtures;

use App\Entity\Editor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Doctrine\Persistence\ObjectManager;

class EditorFixtures extends Fixture
{
    public const EDITOR_REFERENCE = 'editor_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $editor = new Editor();
            $editor->setName($faker->company);
            $editor->setCountry($faker->country);

            $manager->persist($editor);

            $this->addReference(self::EDITOR_REFERENCE . $i, $editor);
        }

        $manager->flush();
    }
}
