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

    public function filtersFindAllSite(array $filters, $user): array
    {
        $queryBuilder = $this->createQueryBuilder('event');

        // Recherche par nom de l'élément
        if (isset($filters['search'])) {
            $queryBuilder->andWhere('event.name like :search') // requête préparée
            ->setParameter('search', "%{$filters['search']}%");
        }

        // Filtre par site
        if (isset($filters['site'])) {
            $queryBuilder->andWhere('event.site = :site')
                ->setParameter('site', $filters['site']);
        }

        // Filtre par organisateur
        if (isset($filters['organizer'])) {
            $queryBuilder->andWhere('event.organizer = :organizer')
                ->setParameter('organizer', $filters['organizer']);
        }

        // Filtre par participant inscrit aux évènements
        if (isset($filters['registered'])) {
            $queryBuilder->innerJoin('event.participants', 'participant')
                ->andWhere('participant.id IN (:registered)')
                ->setParameter('registered', $filters['registered']);
        }

        // Filtre par participant NON inscrit aux évènements
        if (isset($filters['notRegistered'])) {
            $queryBuilder->andWhere('NOT EXISTS (
            SELECT 1
            FROM App\Entity\User userAlias
            WHERE userAlias MEMBER OF event.participants
            AND userAlias.id = :notRegistered
            )')
                ->setParameter('notRegistered', $filters['notRegistered']);
        }

        // Filtre sur date de début
        if(isset($filters['dateStart'])) {
            $queryBuilder->andWhere('event.startAt >= :dateStart')
                ->setParameter('dateStart', $filters['dateStart']);
        }

        // Filtre sur date de fin
        if(isset($filters['dateEnd'])) {
            $queryBuilder->andWhere('event.endAt <= :dateEnd')
                ->setParameter('dateEnd', $filters['dateEnd']);
        }

        // Filtre sur les évènements passés
        if (isset($filters['outingPast'])) {
            $queryBuilder->andWhere('event.startAt < :currentDate')
                ->setParameter('currentDate', new \DateTime());
        }

        // Pas d'affichage pour les events de + d'un mois
        $queryBuilder->andWhere('event.endAt > :maxDate')
            ->setParameter('maxDate', (new \DateTime())->modify('-1 month'));

        // Pas d'affichage pour les status créé et non publié sauf si je suis l'organisateur
        $queryBuilder->andWhere('event.state != :created AND event.isPublished = :true OR event.state = :created AND event.organizer = :organizer AND event.isPublished = :false')
            ->setParameter(':created', 'CREATED')
            ->setParameter(':organizer', $user)
            ->setParameter(':true', true)
            ->setParameter(':false', false);

        // Ne pas afficher les évènements avec groupé privé dont vous n'appartenez pas (sauf si vous etes l'organisateur
        $queryBuilder
            ->leftJoin('event.privateGroup', 'privateGroup')
            ->leftJoin('privateGroup.Users', 'groupUser')
            ->andWhere('event.isPrivate = :false 
            OR (event.isPrivate = :true AND event.organizer = :user) 
            OR (event.isPrivate = :true AND groupUser IN (:userId))')
            ->setParameter(':user', $user)
            ->setParameter(':userId', $user->getId())
            ->setParameter(':true', true)
            ->setParameter(':false', false);

        $queryBuilder->orderBy('event.startAt', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}
