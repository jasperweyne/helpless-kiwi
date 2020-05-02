<?php

namespace App\Repository\Person;

use App\Entity\Order;
use App\Entity\Person\PersonScheme;
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

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonField::class);
    }

    public function findPrependPosition(PersonScheme $scheme)
    {
        $val = $this->createQueryBuilder('f')
            ->where('f.position IS NOT NULL')
            ->andWhere('f.scheme = :scheme')
            ->setParameter('scheme', $scheme)
            ->orderBy('f.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($val)) {
            return Order::avg(self::MINORDER(), self::MAXORDER());
        }

        $current = Order::create($val->getPosition());

        // Six orders of magnitude removed
        return Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current, self::MINORDER()))))));
    }

    public function findAppendPosition(PersonScheme $scheme)
    {
        $val = $this->createQueryBuilder('f')
            ->where('f.position IS NOT NULL')
            ->andWhere('f.scheme = :scheme')
            ->setParameter('scheme', $scheme)
            ->orderBy('f.position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($val)) {
            return Order::avg(self::MINORDER(), self::MAXORDER());
        }

        $current = Order::create($val->getPosition());

        // Six orders of magnitude removed
        return Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current,
               Order::avg($current, self::MAXORDER()))))));
    }

    public function findBefore(PersonScheme $scheme, Order $position)
    {
        $field = $this->createQueryBuilder('f')
            ->where('f.position < :position')
            ->andWhere('f.scheme = :scheme')
            ->setParameter('position', strval($position))
            ->setParameter('scheme', $scheme)
            ->orderBy('f.position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($field))
            return $this->findPrependPosition($scheme);

        return $field->getPosition();
    }

    public function findAfter(PersonScheme $scheme, Order $position)
    {
        $field = $this->createQueryBuilder('f')
            ->where('f.position > :position')
            ->andWhere('f.scheme = :scheme')
            ->setParameter('position', strval($position))
            ->setParameter('scheme', $scheme)
            ->orderBy('f.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;


        if (is_null($field))
            return $this->findAppendPosition($scheme);

        return $field->getPosition();
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
