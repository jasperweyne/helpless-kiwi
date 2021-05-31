<?php

namespace Tests\Integration\Form\Group;

use App\Form\Group\RelationAddType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RelationAddTypeTest.
 *
 * @covers \App\Form\Group\RelationAddType
 */
class RelationAddTypeTest extends KernelTestCase
{
    /**
     * @var RelationAddType
     */
    protected $relationAddType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->relationAddType = new RelationAddType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->relationAddType);
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
