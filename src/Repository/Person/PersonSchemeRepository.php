<?php

namespace App\Repository\Person;

use App\Entity\Person\PersonScheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PersonScheme|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonScheme|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonScheme[]    findAll()
 * @method PersonScheme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonSchemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonScheme::class);
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
