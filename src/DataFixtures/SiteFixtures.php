<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sites = [
            'Saint-Herblain',
            'Quimper',
            'La Roche sur Yon',
            'Niort',
        ];

        foreach ($sites as $index=>$siteName) {
            
            $site = new Site();
            $site->setName($siteName);

            $manager->persist($site);

            $this->addReference('site_'.$index, $site);
        }
        $manager->flush();
    }

}
