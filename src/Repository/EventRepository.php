<?php

namespace App\Repository;

use App\Entity\Event\Event;
use App\Entity\Security\LocalAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param LocalAccount $account
     *
     * @return Event[] Returns an array of Event objects
     */
    public function findEventByUser(LocalAccount $account)
    {
        return $this->createQueryBuilder('m')
            ->where('m.person = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getResult()
        ;
    }
}
