<?php

namespace App\Repository\Document;

use App\Entity\Document\FieldValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FieldValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method FieldValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method FieldValue[]    findAll()
 * @method FieldValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldValue::class);
    }

    // /**
    //  * @return PersonValue[] Returns an array of PersonValue objects
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
    public function findOneBySomeField($value): ?PersonValue
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
