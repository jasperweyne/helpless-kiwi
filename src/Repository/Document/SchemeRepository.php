<?php

namespace App\Repository\Document;

use App\Entity\Document\Scheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Scheme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scheme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scheme[]    findAll()
 * @method Scheme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scheme::class);
    }

    // /**
    //  * @return PersonScheme[] Returns an array of PersonScheme objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PersonScheme
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
