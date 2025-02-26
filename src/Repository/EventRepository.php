<?php

namespace App\Repository;

use App\Entity\Event;
use Couchbase\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findAllEventsWithInscriptionCount(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.id as eventId, e.name, e.description, e.maxParticipant, 
                            e.startAt, e.endAt, e.state, 
                            organizer.id as organizerId, organizer.name as organizerName, organizer.email as organizerEmail, 
                            COUNT(participant.id) as inscriptionCount')
            ->leftJoin('e.participants', 'participant')
            ->leftJoin('e.organizer', 'organizer')
            ->groupBy('e.id', 'e.name', 'e.description', 'e.maxParticipant', 'e.startAt', 'e.endAt', 'e.state', 'organizer.id');
        return $qb->getQuery()->getArrayResult();
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
