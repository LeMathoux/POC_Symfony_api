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

    public function getfutureReleases(\DateTimeImmutable $currentDate){
        $qb = $this->createQueryBuilder('vg')
            ->andWhere('vg.releaseDate > :currentDate')
            ->setParameter('currentDate', $currentDate)
            ->orderBy('vg.releaseDate', 'ASC');
            
        return $qb->getQuery()->getResult();
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
