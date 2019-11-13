<?php

namespace App\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Registration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registration[]    findAll()
 * @method Registration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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
                        ->setParameter('val', $activity)
                        ->getDQL()
                )
            )
            ->andWhere('r.activity = :val')
            ->setParameter('val', $activity)
            ->orderBy('r.deletedate', 'DESC')
            ->groupBy('r.person')
            ->getQuery()
            ->getResult()
        ;
    }
}
