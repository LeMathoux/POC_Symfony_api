<?php

namespace App\Repository;

use App\Entity\VideoGame;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoGame>
 */
class VideoGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoGame::class);
    }

    public function getAllWithPagination($page, $limit){
        $qb = $this->createQueryBuilder('vg')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
            
        return $qb->getQuery()->getResult();
    }

    public function findUpcomingGames(): array
    {
        $today = new \DateTimeImmutable();
        $endDate = $today->modify("+7 days");

        return $this->createQueryBuilder('g')
            ->where('g.releaseDate >= :today')
            ->andWhere('g.releaseDate <= :endDate')
            ->setParameter('today', $today->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $endDate->format('Y-m-d 23:59:59'))
            ->orderBy('g.releaseDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return VideoGame[] Returns an array of VideoGame objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?VideoGame
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
