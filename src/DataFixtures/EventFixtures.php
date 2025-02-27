<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\User;
use App\Enum\EventState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $event = new Event();

            $event->setName($faker->word());
            $event->setDescription($faker->text());
            $event->setMaxParticipant($faker->numberBetween(0, 100));
            $event->setIsPublished($faker->boolean());
            $event->setPhoto($faker->image());
            if ($event->isPublished()){
                $eventCancelled = $faker->boolean();
                if ($eventCancelled){
                    $event->setState(EventState::CANCELLED);
                } else {
                    $event->setState(EventState::OPENED);
                }
            } else {
                $event->setState(EventState::CREATED);
            }

            $startAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+2 months'));
            $inscriptionLimitAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+1 month'));

            // Assurer que la limite d'inscription est avant la date de fin
            $endAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween($startAt->format('Y-m-d H:i:s'), '+2 months'));

            // Assigner les dates à l'événement
            $event->setStartAt($startAt);
            $event->setInscriptionLimitAt($inscriptionLimitAt);
            $event->setEndAt($endAt);

            // Dépendance Site
            $site = $this->getReference('site_'. rand(0, 3), Site::class);
            $event->setSite($site);

            // Dépendance Location
            $location = $this->getReference('location_', Location::class);
            $event->setLocation($location);

            // Dépendance User
            $user = $this->getReference('user_', User::class);
            $event->setOrganizer($user);

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class, LocationFixtures::class, UserFixtures::class];
    }
}
