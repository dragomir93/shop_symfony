<?php

namespace App\Repository;

use App\Entity\AboutUs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AboutUs|null find($id, $lockMode = null, $lockVersion = null)
 * @method AboutUs|null findOneBy(array $criteria, array $orderBy = null)
 * @method AboutUs[]    findAll()
 * @method AboutUs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AboutUsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutUs::class);
    }

    public function findLimitedResults($limit)
    {
        return $this->createQueryBuilder('a')
        ->orderBy('a.id', 'ASC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }
    
    // /**
    //  * @return AboutUs[] Returns an array of AboutUs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AboutUs
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
