<?php

namespace App\Service;

use App\Entity\Event;
use DateTime;

class EventService {
    public function canCancel($event): bool
    {
        return ($event->getState() === Event::OPENED && $event->getStartAt() > (new DateTime())->modify('-1 hour'));
    }

    public function isEventCreator($event, $user): bool
    {
        return ($event && $user && $event->getOrganizer() && $event->getOrganizer()->getId() === $user->getId());
    }
}

