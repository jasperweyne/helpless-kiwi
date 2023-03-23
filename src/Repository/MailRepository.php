<?php

namespace App\Repository;

use App\Entity\Mail\Mail;
use App\Entity\Security\LocalAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mail>
 *
 * @method Mail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mail[]    findAll()
 * @method Mail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mail::class);
    }

    /**
     * @param LocalAccount $account
     *
     * @return Mail[] Returns an array of Mail objects
     */
    public function findMailByUser(LocalAccount $account)
    {
        return $this->createQueryBuilder('m')
            ->where(':account MEMBER OF m.recipients')
            ->setParameter('account', $account)
            ->getQuery()
            ->getResult()
        ;
    }
}
