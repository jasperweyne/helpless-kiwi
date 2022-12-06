<?php

namespace App\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Order;
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
    /** @var ?Order */
    private static $min;

    /** @var ?Order */
    private static $max;

    public static function MINORDER(): Order
    {
        if (is_null(self::$min)) {
            self::$min = Order::create('aaaaaaaaaaaaaaaa');
        }

        return self::$min;
    }

    public static function MAXORDER(): Order
    {
        if (is_null(self::$max)) {
            self::$max = Order::create('zzzzzzzzzzzzzzzz');
        }

        return self::$max;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function findPrependPosition(Activity $activity): Order
    {
        /** @var ?Registration */
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
        return Order::avg(
            $current,
            Order::avg(
                $current,
                Order::avg(
                    $current,
                    Order::avg(
                        $current,
                        Order::avg(
                            $current,
                            Order::avg($current, self::MINORDER())
                        )
                    )
                )
            )
        );
    }

    public function findAppendPosition(Activity $activity): Order
    {
        /** @var ?Registration */
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
        return Order::avg(
            $current,
            Order::avg(
                $current,
                Order::avg(
                    $current,
                    Order::avg(
                        $current,
                        Order::avg(
                            $current,
                            Order::avg($current, self::MAXORDER())
                        )
                    )
                )
            )
        );
    }

    public function findBefore(Activity $activity, Order $position): Order
    {
        /** @var ?Registration */
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

        if (is_null($reg)) {
            return $this->findPrependPosition($activity);
        }

        return $reg->getReservePosition();
    }

    public function findAfter(Activity $activity, Order $position): Order
    {
        /** @var ?Registration */
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

        if (is_null($reg)) {
            return $this->findAppendPosition($activity);
        }

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
                        ->andWhere('b.person IS NOT NULL')
                        ->setParameter('val', $activity)
                        ->getDQL()
                )
            )
            ->andWhere('r.activity = :val')
            ->andWhere('r.reserve_position IS NULL')
            ->andWhere('r.deletedate IS NOT NULL')
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

    /**
     * @return int Returns an integer
     */
    public function countPresent(Activity $activity)
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.deletedate IS NULL')
            ->andWhere('p.activity = :activity')
            ->andWhere('p.present = TRUE')
            ->setParameter('activity', $activity)
            ->getQuery()
            ->getSingleScalarResult()
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
