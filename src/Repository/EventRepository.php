<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function filtersFindAllSite(array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('event');
        if (isset($filters['search'])) {
            $queryBuilder->andWhere('event.name like :search') // requête préparée
                ->setParameter('search', "%{$filters['search']}%");
        }
        // Si le site est sélectionné : afficher uniquement les évènements de ce site
        if (isset($filters['site'])) {
            $queryBuilder->andWhere('event.site = :site')
                ->setParameter('site', $filters['site']);
        }
        if (isset($filters['organizer'])) {
            $queryBuilder->andWhere('event.organizer = :organizer')
                ->setParameter('organizer', $filters['organizer']);
        }
        if (isset($filters['registered'])) {
            $queryBuilder->innerJoin('event.participants', 'participant')
                ->andWhere('participant.id IN (:registered)')
                ->setParameter('registered', $filters['registered']);
        }
        if (isset($filters['notRegistered'])) {
            $queryBuilder->andWhere('NOT EXISTS (
            SELECT 1
            FROM App\Entity\User userAlias
            WHERE userAlias MEMBER OF event.participants
            AND userAlias.id = :notRegistered
            )')
                ->setParameter('notRegistered', $filters['notRegistered']);
        }


        return $queryBuilder->getQuery()->getResult();
    }
}
