<?php

namespace App\Repository;

use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * @return Group[] Returns an array of Group objects
     */
    public function findSubGroupsFor(Group $group)
    {
        $allsubgroups = [];
        $qb = $this->createQueryBuilder('g');
        $subgroups = $qb
            ->andWhere('g.parent = :id')
            ->setParameter('id', $group->getId())
            ->getQuery()
            ->getResult();

        foreach ($subgroups as $subgroup) {
            $allsubgroups = array_merge($allsubgroups, [...$this->findSubGroupsFor($subgroup)]);
            array_push($allsubgroups, $subgroup);
        }

        return $allsubgroups;
    }

    /**
     * @return Group[] Returns an array of Group objects
     */
    public function findSubGroupsForPerson(LocalAccount $person)
    {
        $groups = $person->getRelations()->toArray();
        $allgroups = $groups; // creates copy
        foreach ($groups as $group) {
            $allgroups = array_merge($allgroups, $this->findSubGroupsFor($group));
        }

        return $allgroups;
    }

    // /**
    //  * @return Group[] Returns an array of Group objects
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
    public function findOneBySomeField($value): ?Group
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
