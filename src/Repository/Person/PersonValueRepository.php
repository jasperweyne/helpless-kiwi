<?php

namespace App\Repository\Person;

use App\Entity\Person\PersonValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PersonValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonValue[]    findAll()
 * @method PersonValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonValue::class);
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
