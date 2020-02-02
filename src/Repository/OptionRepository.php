<?php

namespace App\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Activity\PriceOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PriceOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceOption[]    findAll()
 * @method PriceOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PriceOption::class);
    }

    /**
     * @return PriceOption[] Returns an array of PriceOption objects
     */
    public function findUpcomingByGroup(Activity $activity,$groups)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.activity = :activity')
            ->andWhere('(o.target IN (:groups)) OR (o.target is NULL)')
            ->setParameter('activity', $activity)
            ->setParameter('groups', $groups)
            ->getQuery()
            ->getResult()
        ;
    }
    
}
