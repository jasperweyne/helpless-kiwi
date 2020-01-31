<?php

namespace App\Repository;

use App\Entity\Activity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @return Activity[] Returns an array of Activity objects
     */
    public function findUpcoming()
    {       
        return $this->createQueryBuilder('p')
            ->andWhere('p.end > CURRENT_TIMESTAMP()')
            ->orderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Activity[] Returns an array of Activity objects
     */
    public function findUpcomingByGroup($groups)
    {       
        return $this->createQueryBuilder('p')
            ->andWhere('p.end > CURRENT_TIMESTAMP()')
            ->andWhere('(p.target IN (:groups)) OR (p.target is NULL)')
            ->setParameter('groups', $groups)
            ->orderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    // /**
    //  * @return Activity[] Returns an array of Activity objects
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
    public function findOneBySomeField($value): ?Activity
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
