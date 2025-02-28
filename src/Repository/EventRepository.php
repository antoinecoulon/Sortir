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

    /**
     * @param int $eventId
     * @return int
     * @throws Exception
     */
    public function findInscriptionCount(int $eventId): int
    {
        try {
            $connexion = $this->getEntityManager()->getConnection();
            $sql = '
                SELECT COUNT(user_id) FROM event_user
                WHERE event_id = :eventId
            ';
            $result = $connexion->executeQuery($sql, ['eventId' => $eventId])->fetchNumeric();
            return $result[0];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

//    public function findInscriptionCountByEventId(int $eventId): int
//    {
//        $connect = $this->getEntityManager()->getConnection();
//
//        $sql = '';
//    }

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
