<?php

namespace Tests\Unit\Security;

use App\Entity\Activity\Activity;
use App\Entity\Security\LocalAccount;
use App\Security\ActivityVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class ActivityVoterTest.
 *
 * @covers \App\Security\ActivityVoter
 */
class ActivityVoterTest extends KernelTestCase
{
    private function createUser(bool $admin): LocalAccount
    {
        $roles = ['ROLE_USER'];
        if ($admin) {
            $roles[] = 'ROLE_ADMIN';
        }

        /** @var LocalAccount&MockObject $user */
        $user = $this->createMock(LocalAccount::class);
        $user->method('getRoles')->willReturn($roles);

        return $user;
    }

    private function createActivity(bool $visible): Activity
    {
        /** @var Activity&MockObject $activity */
        $activity = $this->createMock(Activity::class);
        $activity->method('isVisibleBy')->willReturn($visible);

        return $activity;
    }

    /**
     * @return iterable<array{Activity, ?LocalAccount, int}>
     */
    public function provideCases()
    {
        yield 'anonymous cannot view a non-visible activity' => [
            $this->createActivity(false),
            null,
            Voter::ACCESS_DENIED,
        ];

        yield 'anonymous can view a visible activity' => [
            $this->createActivity(true),
            null,
            Voter::ACCESS_GRANTED,
        ];

        yield 'non-admin cannot view a non-visible activity' => [
            $this->createActivity(false),
            $this->createUser(false),
            Voter::ACCESS_DENIED,
        ];

        yield 'non-admin can view a visible activity' => [
            $this->createActivity(true),
            $this->createUser(false),
            Voter::ACCESS_GRANTED,
        ];

        yield 'admin can always view a non-visible activity' => [
            $this->createActivity(false),
            $this->createUser(true),
            Voter::ACCESS_GRANTED,
        ];

        yield 'admin can always view a visible activity' => [
            $this->createActivity(true),
            $this->createUser(true),
            Voter::ACCESS_GRANTED,
        ];
    }

    /**
     * @dataProvider provideCases
     */
    public function testVote(
        Activity $activity,
        ?LocalAccount $user,
        int $expectedVote,
    ): void {
        $voter = new ActivityVoter();

        $token = new NullToken();
        if (null !== $user) {
            $token = new UsernamePasswordToken(
                $user,
                'memory'
            );
        }

        self::assertSame(
            $expectedVote,
            $voter->vote($token, $activity, [ActivityVoter::VIEW])
        );
    }

    public function testDoesNotSupportOtherSubjects(): void
    {
        $voter = new ActivityVoter();
        $token = new NullToken();

        self::assertSame(
            Voter::ACCESS_ABSTAIN,
            $voter->vote($token, new \stdClass(), [ActivityVoter::VIEW])
        );
    }

    public function testDoesNotSupportOtherAttributes(): void
    {
        $voter = new ActivityVoter();
        $token = new NullToken();

        self::assertSame(
            Voter::ACCESS_ABSTAIN,
            $voter->vote($token, $this->createActivity(true), ['some_other_attribute'])
        );
    }
}
