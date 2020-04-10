<?php

namespace App\Repository\Person;

use App\Entity\Person\PersonField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PersonField|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonField|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonField[]    findAll()
 * @method PersonField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonField::class);
    }

    // /**
    //  * @return PersonField[] Returns an array of PersonField objects
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
    public function findOneBySomeField($value): ?PersonField
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
