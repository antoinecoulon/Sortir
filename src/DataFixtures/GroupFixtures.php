<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Group;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $groups = [
            'Les BG',
            'Rastapoulos',
            'Geeked',
            'Kats'
        ];


        foreach ($groups as $name) {
            $group = new Group();

            $group->setName($name);

            for($i=0; $i < 2; $i++) {
                $event = $this->getReference('private_event_' . rand(0, 3), Event::class);
                $group->addEvent($event);
            }

            for($i=0; $i < 5; $i++) {
                $user = $this->getReference('user_' . rand(4, 9), User::class);
                $group->addUser($user);
            }
            $creator = $this->getReference('user_' . rand(0, 3), User::class);
            $group->setCreator($creator);
            $manager->persist($group);
        }
        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EventFixtures::class
        ];
    }
}
