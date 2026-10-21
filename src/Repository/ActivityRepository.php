<?php

namespace App\Repository;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 *
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @param Group[] $groups
     *
     * @return Activity[] Returns an array of Activity objects
     */
    public function findAuthor($groups)
    {
        return $this->createAuthorQueryBuilder($groups)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Group[] $groups
     *
     * @return Activity[] Returns an array of Activity objects
     */
    public function findAuthorArchive($groups)
    {
        return $this->createAuthorArchiveQueryBuilder($groups)
            ->getQuery()
            ->getResult();
    }

    /**
     * QueryBuilder variant of {@see findAuthor()}, for use with pagination (avoids
     * hydrating the full result set up front).
     *
     * @param Group[] $groups
     */
    public function createAuthorQueryBuilder($groups): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = false')
            ->andWhere('(p.author IN (:groups))')
            ->setParameter('groups', $groups)
            ->orderBy('p.start', 'DESC')
        ;
    }

    /**
     * QueryBuilder variant of {@see findAuthorArchive()}, for use with pagination (avoids
     * hydrating the full result set up front).
     *
     * @param Group[] $groups
     */
    public function createAuthorArchiveQueryBuilder($groups): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = true')
            ->andWhere('(p.author IN (:groups))')
            ->setParameter('groups', $groups)
            ->orderBy('p.start', 'DESC')
        ;
    }

    /**
     * @return Activity[] Returns an array of Activity objects
     */
    public function findUpcoming()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = false')
            ->andWhere('p.end > CURRENT_TIMESTAMP()')
            ->orderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns an array of Activity objects
     */
    public function findActive()
    {
        return $this->createActiveQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * QueryBuilder variant of {@see findActive()}, for use with pagination (avoids
     * hydrating the full result set up front).
     */
    public function createActiveQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = false')
            ->orderBy('p.start', 'DESC')
        ;
    }

    /**
     * @return Activity[] Returns an array of Activity objects
     */
    public function findArchived()
    {
        return $this->createArchivedQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * QueryBuilder variant of {@see findArchived()}, for use with pagination (avoids
     * hydrating the full result set up front).
     */
    public function createArchivedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = true')
            ->orderBy('p.start', 'DESC')
        ;
    }

    /**
     * @param Group[] $groups
     *
     * @return Activity[] Returns an array of Activity objects
     */
    public function findUpcomingByGroup($groups)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = false')
            ->andWhere('p.end > CURRENT_TIMESTAMP()')
            ->andWhere('(p.target IN (:groups)) OR (p.target is NULL)')
            ->setParameter('groups', $groups)
            ->orderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Group[] $groups
     *
     * @return Activity[] Returns an array of Activity objects
     */
    public function findVisibleUpcomingByGroup($groups)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.archived = false')
            ->andWhere('p.end > CURRENT_TIMESTAMP()')
            ->andWhere('(p.target IN (:groups)) OR (p.target is NULL)')
            ->andWhere('p.visibleAfter IS NOT NULL')
            ->andWhere('p.visibleAfter < CURRENT_TIMESTAMP()')
            ->setParameter('groups', $groups)
            ->orderBy('p.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns all activities that a user has been registered for
     */
    public function findRegisteredFor(LocalAccount $person)
    {
        return $this->createQueryBuilder('act')
            ->join(Registration::class, 'reg')
            ->andWhere('reg.person = :person')
            ->setParameter('person', $person)
            ->andWhere('act.archived = false')
            ->andWhere('reg.deletedate IS NULL')
            ->orderBy('act.start', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
