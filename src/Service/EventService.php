<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use DateTime;
use Symfony\Component\Security\Core\User\UserInterface;

class EventService {
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

    public function filtersEvent(array $params, $user) : array {
        $filters = [];

        if (isset($params['search']) && !empty($params['search'])) {
            $filters['search'] = $params['search'];
        }

        if (isset($params['site']) && !empty($params['site'])) {
            $filters['site'] = $params['site'];
        }

        // Vérifie si la checkbox "Je suis organisateur/trice" est cochée :
        if (isset($params['organizer'])){
            $filters['organizer'] = $user;
        }
        // Vérifie si la checkbox "Je suis inscrit.e" est cochée :
        if (isset($params['registered'])) {
            $filters['registered'] = $user;
        }

        // Vérifie si la checkbox "Je ne suis pas inscrit.e" est cochée :
        if (isset($params['notRegistered'])) {
            $filters['notRegistered'] = $user;
        }

        // Vérifie si la checkbox "Sorties passées" est cochée :
        if (isset($params['outingPast'])) {
            $filters['outingPast'] = new DateTime();
        }

        if (isset($params['dateStart']) && !empty($params['dateStart'])) {
            try {
                // Vérifier si la date est ok et la passer en dateTime
                $startAtTime = new DateTime($params['dateStart']);
                $filters['dateStart'] = $startAtTime;
            } catch (\Exception $e) {
            }
        }

        if (isset($params['dateEnd']) && !empty($params['dateEnd'])) {
            try {
                // Vérifier si la date est ok et la passer en dateTime
                $endAtTime = new DateTime($params['dateEnd']);
                $filters['dateEnd'] = $endAtTime;
            } catch (\Exception $e) {
            }
        }

        return $filters;
    }
}

