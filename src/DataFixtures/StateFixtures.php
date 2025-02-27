<?php

namespace App\DataFixtures;

use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $states = [
            'CREATED',
            'OPENED',
            'CLOSED',
            'PROCESSING',
            'FINISHED',
            'CANCELLED'
        ];

        foreach ($states as $key=>$state) {
            $state = new State($state);
            $manager->persist($state);
            $this->addReference('state_'.$states[$key], $state);
        }
        $manager->flush();
    }

}
