<?php

namespace App\Repository;

use App\Entity\Security\ApiToken;
use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 *
 * @method ApiToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiToken[]    findAll()
 * @method ApiToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function generate(
        LocalAccount $account,
        TrustedClient $client,
        \DateTimeImmutable $expiresAt = new \DateTimeImmutable('+5 minutes')
    ): string {
        $em = $this->getEntityManager();

        $em->persist($apiToken = new ApiToken($account, $client, $expiresAt));
        $em->flush();

        return $apiToken->token;
    }

    /**
     * Cleanup expired tokens and return the number of cleaned up tokens.
     */
    public function cleanup(): int
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < CURRENT_TIMESTAMP()')
            ->getQuery()
            ->execute()
        ;
    }
}
