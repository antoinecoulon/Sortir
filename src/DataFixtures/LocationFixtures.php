<?php

namespace App\DataFixtures;

use App\Entity\Location;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $location = new Location();

            $location->setName($faker->word());
            $location->setStreet($faker->streetName());
            $location->setStreetNumber($faker->buildingNumber());
            $location->setCity($faker->city());
            $location->setCp($faker->postcode());
            $location->setLongitude($faker->longitude());
            $location->setLatitude($faker->latitude());

            $manager->persist($location);
            $this->setReference('location_'.$i, $location);
        }
        $manager->flush();

    }
}
