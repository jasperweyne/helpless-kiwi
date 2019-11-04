<?php

namespace App\Repository;

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
    public function findByUniqueDeregistrations()
    {
        $qb = $this->createQueryBuilder('p');

        $subq = $this->createQueryBuilder('ps')
        ->Where('ps.deletedate IS NULL')
        ;

        $subq2 = $this->createQueryBuilder('ps')
        ->select('ps.person')
        ->Where('ps.deletedate IS NULL')
        ->groupBy('ps.person')
        ;

        $mqb = $this->createQueryBuilder('p');

        $mqb->where($mqb->expr()->notIn('p.person', $subq->getDQL()));

        $res2 = $mqb->getQuery()->getResult();

        $res1 = $this->createQueryBuilder('p')
        ->andWhere('p.deletedate IS NOT NULL')
        ->groupBy('p.person')
        ->getQuery()
        ->getResult()
        ;

        return $res2;
    }
}
