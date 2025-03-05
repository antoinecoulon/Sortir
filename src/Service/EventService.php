<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use DateTime;
use Symfony\Component\Security\Core\User\UserInterface;

class EventService {

    /**
     * @param Event $event
     * @return bool
     * @throws \DateMalformedStringException
     */
    public function canCancel(Event $event): bool
    {
        return ($event->getState() === Event::OPENED && $event->getStartAt() > (new DateTime())->modify('-1 hour'));
    }

    /**
     * @param Event $event
     * @param UserInterface $user
     * @return bool
     */
    public function isEventCreator(Event $event, UserInterface $user): bool
    {
        if($user instanceof User) {
            return ($event->getOrganizer() && $event->getOrganizer()->getId() === $user->getId());
        }
        return false;
    }
}

