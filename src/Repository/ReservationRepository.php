<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\ReservationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    private const OPEN_STATUSES = [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value];

    /**
     * Réservations à retirer dans les commerces d'un commerçant.
     *
     * @return Reservation[]
     */
    public function findUpcomingForOwner(User $owner): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('o', 'c', 'u')
            ->join('r.offer', 'o')
            ->join('o.commerce', 'c')
            ->join('r.user', 'u')
            ->andWhere('c.owner = :owner')
            ->andWhere('r.status IN (:statuses)')
            ->setParameter('owner', $owner)
            ->setParameter('statuses', self::OPEN_STATUSES)
            ->orderBy('o.pickup_start', 'ASC')
            ->addOrderBy('r.reserved_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOpenByCodeForOwner(User $owner, string $code): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->join('r.offer', 'o')
            ->join('o.commerce', 'c')
            ->andWhere('c.owner = :owner')
            ->andWhere('r.pickupCode = :code')
            ->andWhere('r.status IN (:statuses)')
            ->setParameter('owner', $owner)
            ->setParameter('code', $code)
            ->setParameter('statuses', self::OPEN_STATUSES)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array{saved: int, revenue: float}
     */
    public function getCollectedStatsForOwner(User $owner): array
    {
        $row = $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.quantity), 0) AS saved', 'COALESCE(SUM(o.discounted_price * r.quantity), 0) AS revenue')
            ->join('r.offer', 'o')
            ->join('o.commerce', 'c')
            ->andWhere('c.owner = :owner')
            ->andWhere('r.status = :collected')
            ->setParameter('owner', $owner)
            ->setParameter('collected', ReservationStatus::Collected->value)
            ->getQuery()
            ->getSingleResult();

        return ['saved' => (int) $row['saved'], 'revenue' => (float) $row['revenue']];
    }

//    /**
//     * @return Reservation[] Returns an array of Reservation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reservation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
