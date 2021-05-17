<?php

namespace Tests\Integration\Form\Group;

use App\Form\Group\RelationType;
use App\Provider\Person\PersonRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RelationTypeTest.
 *
 * @covers \App\Form\Group\RelationType
 */
class RelationTypeTest extends KernelTestCase
{
    /**
     * @var RelationType
     */
    protected $relationType;

    /**
     * @var PersonRegistry
     */
    protected $personRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->personRegistry = self::$container->get(PersonRegistry::class);
        $this->relationType = new RelationType($this->personRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->relationType);
        unset($this->personRegistry);
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
