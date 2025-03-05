<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EventService {

    private readonly EntityManagerInterface $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function canCancel(Event $event): bool
    {
        return ($event->getState() === Event::OPENED && $event->getStartAt() > (new DateTime())->modify('-1 hour'));
    }

    public function isEventCreator(Event $event, UserInterface $user): bool
    {
        if($user instanceof User) {
            return ($event->getOrganizer() && $event->getOrganizer()->getId() === $user->getId());
        }
        return false;
    }

    public function updateEventState(): void
    {
        $now = new \DateTime();
        $events = $this->em->getRepository(Event::class)->findAll();
        foreach ($events as $event) {
            if ($event->getEndAt() < $now && $event->getState() !== 'CANCELLED' && $event->getState() !== 'FINISHED') {
                $event->setState('FINISHED');
                $this->em->persist($event);
            } else if ($event->getStartAt() < $now && $event->getEndAt() > $now && $event->getState() !== 'CANCELLED' && $event->getState() !== 'PROCESSING' && $event->getState() !== 'FINISHED') {
                $event->setState('PROCESSING');
                $this->em->persist($event);
            } else if($event->getInscriptionLimitAt() < $now && $event->getStartAt() < $now && $event->getState() !== 'CANCELLED' && $event->getState() !== 'CLOSED' && $event->getState() !== 'FINISHED'){
                $event->setState('CLOSED');
                $this->em->persist($event);
            }
        }
        $this->em->flush();
    }
}

