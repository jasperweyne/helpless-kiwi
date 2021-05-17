<?php

namespace Tests\Integration\Form\Group;

use App\Form\Group\GroupType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupTypeTest.
 *
 * @covers \App\Form\Group\GroupType
 */
class GroupTypeTest extends KernelTestCase
{
    /**
     * @var GroupType
     */
    protected $groupType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->groupType = new GroupType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupType);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
