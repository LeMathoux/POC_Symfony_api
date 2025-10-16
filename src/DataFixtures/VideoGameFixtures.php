<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Editor;
use App\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public const VIDEOGAME_REFERENCE = 'videogame_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $videoGame = new VideoGame();
            $videoGame->setTitle($faker->sentence(3));
            $videoGame->setReleaseDate(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-10 years', 'now')));

            $randomEditor = $this->getReference(EditorFixtures::EDITOR_REFERENCE . $faker->numberBetween(0, 4), Editor::class);
            $videoGame->setEditor($randomEditor);

            $randomCategories = $faker->randomElements(range(0, 9), rand(1, 3));
            foreach ($randomCategories as $catIndex) {
                $category = $this->getReference(CategoryFixtures::CATEGORY_REFERENCE . $catIndex, Category::class);
                $videoGame->addCategory($category);
            }

            $manager->persist($videoGame);

            $this->addReference(self::VIDEOGAME_REFERENCE . $i, $videoGame);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EditorFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
