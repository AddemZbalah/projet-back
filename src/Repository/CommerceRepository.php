<?php

namespace App\Repository;

use App\Entity\Commerce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commerce>
 */
class CommerceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commerce::class);
    }

    /**
     * Commerces situés à moins de $meters mètres du point donné, du plus proche au plus loin.
     * Le calcul de distance est fait par MySQL (ST_Distance_Sphere) sur la colonne POINT `location`.
     *
     * @return list<array{commerce: Commerce, distance: float}> distance en mètres
     */
    public function findNear(float $lat, float $lng, int $meters = 2000, int $limit = 50): array
    {
        $sql = 'SELECT id, ST_Distance_Sphere(location, ST_GeomFromText(:origin)) AS distance
                FROM commerce
                WHERE location IS NOT NULL
                HAVING distance <= :meters
                ORDER BY distance ASC
                LIMIT '.max(1, $limit);

        $rows = $this->getEntityManager()->getConnection()
            ->executeQuery($sql, [
                // WKT : longitude d'abord, puis latitude.
                'origin' => sprintf('POINT(%F %F)', $lng, $lat),
                'meters' => $meters,
            ])
            ->fetchAllAssociative();

        if ($rows === []) {
            return [];
        }

        $commerces = [];
        foreach ($this->findBy(['id' => array_column($rows, 'id')]) as $commerce) {
            $commerces[$commerce->getId()] = $commerce;
        }

        // On garde l'ordre par distance renvoyé par MySQL.
        $results = [];
        foreach ($rows as $row) {
            if (isset($commerces[(int) $row['id']])) {
                $results[] = ['commerce' => $commerces[(int) $row['id']], 'distance' => (float) $row['distance']];
            }
        }

        return $results;
    }

//    /**
//     * @return Commerce[] Returns an array of Commerce objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Commerce
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
