<?php

namespace App\Repository;

use App\Entity\Activity\Order;
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
    private static $min;

    private static $max;

    public static function MINORDER()
    {
        if (is_null(self::$min)) {
            self::$min = Order::create('aaaaaaaaaaaaaaaa');
        }

        return self::$min;
    }

    public static function MAXORDER()
    {
        if (is_null(self::$max)) {
            self::$max = Order::create('zzzzzzzzzzzzzzzz');
        }

        return self::$max;
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function findPrependPosition(Activity $activity)
    {
        $val = $this->createQueryBuilder('r')
            ->where('r.reserve_position IS NOT NULL')
            ->andWhere('r.activity = :activity')
            ->andWhere('r.deletedate IS NULL')
            ->setParameter('activity', $activity)
            ->orderBy('r.reserve_position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($val)) {
            return Order::avg(self::MINORDER(), self::MAXORDER());
        }

        $current = Order::create($val->getReservePosition());

        // Six orders of magnitude removed
        return Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current, self::MINORDER()))))));
    }

    public function findAppendPosition(Activity $activity)
    {
        $val = $this->createQueryBuilder('r')
            ->where('r.reserve_position IS NOT NULL')
            ->andWhere('r.activity = :activity')
            ->andWhere('r.deletedate IS NULL')
            ->setParameter('activity', $activity)
            ->orderBy('r.reserve_position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($val)) {
            return Order::avg(self::MINORDER(), self::MAXORDER());
        }

        $current = Order::create($val->getReservePosition());

        // Six orders of magnitude removed
        return Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current, self::MAXORDER()))))));
    }

    public function findBefore(Activity $activity, Order $position)
    {
        $reg = $this->createQueryBuilder('r')
            ->where('r.reserve_position < :position')
            ->andWhere('r.activity = :activity')
            ->andWhere('r.deletedate IS NULL')
            ->setParameter('position', strval($position))
            ->setParameter('activity', $activity)
            ->orderBy('r.reserve_position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($reg))
            return self::MINORDER();

        return $reg->getReservePosition();
    }

    public function findAfter(Activity $activity, Order $position)
    {
        $reg = $this->createQueryBuilder('r')
            ->where('r.reserve_position > :position')
            ->andWhere('r.activity = :activity')
            ->andWhere('r.deletedate IS NULL')
            ->setParameter('position', strval($position))
            ->setParameter('activity', $activity)
            ->orderBy('r.reserve_position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;


        if (is_null($reg))
            return self::MAXORDER();

        return $reg->getReservePosition();
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
                        ->andWhere('b.reserve_position IS NULL')
                        ->andWhere('b.activity = :val')
                        ->setParameter('val', $activity)
                        ->getDQL()
                )
            )
            ->andWhere('r.activity = :val')
            ->andWhere('r.reserve_position IS NULL')
            ->setParameter('val', $activity)
            ->orderBy('r.deletedate', 'DESC')
            ->groupBy('r.person')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Registration[] Returns an array of Registration objects
     */
    public function findReserve(Activity $activity)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.reserve_position IS NOT NULL')
            ->andWhere('p.activity = :activity')
            ->andWhere('p.deletedate IS NULL')
            ->setParameter('activity', $activity)
            ->orderBy('p.reserve_position', 'ASC')
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
