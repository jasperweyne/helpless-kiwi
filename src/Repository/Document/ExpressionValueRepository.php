<?php

namespace App\Repository\Document;

use App\Entity\Document\ExpressionValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ExpressionValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpressionValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpressionValue[]    findAll()
 * @method ExpressionValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpressionValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpressionValue::class);
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
