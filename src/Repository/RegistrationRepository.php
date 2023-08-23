<?php

namespace App\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Registration>
 *
 * @method Registration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registration[]    findAll()
 * @method Registration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    /**
     * @return Registration[] Returns an array of Activity objects
     */
    public function findDeregistrations(Activity $activity)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->where(
                $qb->expr()->notIn(
                    'r.person',
                    $this->createQueryBuilder('b')
                        ->select('IDENTITY(b.person)')
                        ->where('b.deletedate IS NULL')
                        ->andWhere('b.activity = :val')
                        ->andWhere('b.person IS NOT NULL')
                        ->setParameter('val', $activity)
                        ->getDQL()
                )
            )
            ->andWhere('r.activity = :val')
            ->andWhere('r.deletedate IS NOT NULL')
            ->setParameter('val', $activity)
            ->orderBy('r.deletedate', 'DESC')
            ->groupBy('r.person')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Registration[] Returns an array of Registration objects
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
    public function findOneBySomeField($value): ?Registration
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
