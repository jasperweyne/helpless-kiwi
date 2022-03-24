<?php

namespace Tests\Integration\Form\Group;

use App\Form\Group\RelationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RelationTypeTest.
 *
 * @covers \App\Form\Group\RelationType
 */
class RelationTypeTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->relationType = new RelationType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->relationType);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
