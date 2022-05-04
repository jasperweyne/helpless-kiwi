<?php

namespace Tests\Unit\Security;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Entity\Security\LocalAccount;
use App\Security\GroupVoter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class GroupVoterTest.
 *
 * @covers \App\Security\GroupVoter
 */
class GroupVoterTest extends KernelTestCase
{
    private function createUser(bool $admin, Group ...$groups): LocalAccount
    {
        $roles = ['ROLE_USER'];
        if ($admin) {
            $roles[] = 'ROLE_ADMIN';
        }

        /** @var LocalAccount&MockObject $user */
        $user = $this->createMock(LocalAccount::class);
        $user->method('getRoles')->willReturn($roles);

        $rels = [];
        foreach ($groups as $group) {
            $rel = $this->createMock(Relation::class);
            $rel->method('getGroup')->willReturn($group);
            $rels[] = $rel;
        }

        $user->method('getRelations')->willReturn(new ArrayCollection($rels));

        return $user;
    }

    public function provideCases()
    {
        $group1 = new Group();
        $group2 = new Group();
        $inactive = new Group();
        $group1->setActive(true);
        $group2->setActive(true);

        yield 'anonymous cannot pass' => [
            GroupVoter::IN_GROUP,
            $group2,
            null,
            Voter::ACCESS_DENIED,
        ];

        yield 'non-author cannot pass' => [
            GroupVoter::IN_GROUP,
            $group1,
            $this->createUser(false, $group2),
            Voter::ACCESS_DENIED,
        ];

        yield 'author can pass' => [
            GroupVoter::IN_GROUP,
            $group1,
            $this->createUser(false, $group1),
            Voter::ACCESS_GRANTED,
        ];

        yield 'author of inactive cannot pass' => [
            GroupVoter::IN_GROUP,
            $inactive,
            $this->createUser(false, $inactive),
            Voter::ACCESS_DENIED,
        ];

        yield 'admin can always pass' => [
            GroupVoter::IN_GROUP,
            $group2,
            $this->createUser(true, $group1),
            Voter::ACCESS_GRANTED,
        ];

        yield 'non-author cannot pass any' => [
            GroupVoter::ANY_GROUP,
            null,
            $this->createUser(false),
            Voter::ACCESS_DENIED,
        ];

        yield 'author can pass any' => [
            GroupVoter::ANY_GROUP,
            null,
            $this->createUser(false, $group1),
            Voter::ACCESS_GRANTED,
        ];
    }

    /**
     * @dataProvider provideCases
     */
    public function testVote(
        string $attribute,
        ?Group $group,
        ?LocalAccount $user,
        $expectedVote
    ) {
        $voter = new GroupVoter();

        $token = new AnonymousToken('secret', 'anonymous');
        if ($user) {
            $token = new UsernamePasswordToken(
                $user, 'credentials', 'memory'
            );
        }

        self::assertSame(
            $expectedVote,
            $voter->vote($token, $group, [$attribute])
        );
    }
}
