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
    const MINORDER = 'aaaaaaaaaaaaaaaa';

    const MAXORDER = 'zzzzzzzzzzzzzzzz';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function findPrependPosition(Activity $activity)
    {
        $current = $this->createQueryBuilder('r')
            ->where('r.reserve_position IS NOT NULL')
            ->andWhere('r.activity = :activity')
            ->setParameter('activity', $activity)
            ->orderBy('r.reserve_position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($current)) {
            return $this->between(self::MINORDER, self::MAXORDER);
        }

        // Six orders of magnitude removed
        return $this->between(
               $this->between(
               $this->between(
               $this->between(
               $this->between(
               $this->between(self::MINORDER, $current), $current), $current), $current), $current)
               );
    }

    public function findAppendPosition(Activity $activity)
    {
        $current = $this->createQueryBuilder('r')
            ->where('r.reserve_position IS NOT NULL')
            ->andWhere('r.activity = :activity')
            ->setParameter('activity', $activity)
            ->orderBy('r.reserve_position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (is_null($current)) {
            return $this->between(self::MINORDER, self::MAXORDER);
        }

        // Six orders of magnitude removed
        return $this->between($current,
               $this->between($current,
               $this->between($current,
               $this->between($current,
               $this->between($current,
               $this->between($current, self::MAXORDER))))));
    }

    public function positionBetween(string $low, string $high)
    {
        /// Convert to arrays of integers
        $strToIdx = function ($x) {
            return ord($x) - ord('a');
        };
        $lx = array_map($strToIdx, str_split($low));
        $hx = array_map($strToIdx, str_split($high));

        // Pad arrays to have equal length
        $length = max(count($lx), count($hx));
        $il = array_pad($lx, $length, 0);
        $ih = array_pad($hx, $length, 0);

        // Check if input is equal
        if ($il === $ih) {
            throw new \LogicException("Values can't be equal");
        }

        // Calculate average value
        // Divide by two, carrying over to next elements
        $carry = 0;
        $interm = [];
        for ($i = 0; $i < count($il); ++$i) {
            $avg = ($ih[$i] + $il[$i]) / 2;

            // Calculate result
            $interm[$i] = floor($avg) + $carry;

            // Set carry for the next iteration
            $carry = floor(($avg - floor($avg)) * 26);
        }

        // Reverse carry for overflowing elements
        $carry = 0;
        $result = $interm;
        for ($i = count($il) - 1; $i >= 0; --$i) {
            // Calculate result
            $result[$i] = ($interm[$i] + $carry) % 26;

            // Set carry for the next iteration
            $carry = floor($interm[$i] / 26);
        }

        // Convert to string
        $idxToStr = function ($x) {
            return chr($x + ord('a'));
        };

        return implode(array_map($idxToStr, $result));
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
