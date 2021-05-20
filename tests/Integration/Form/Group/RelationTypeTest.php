<?php

namespace Tests\Integration\Form\Group;

use App\Form\Group\RelationType;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::$container->get(EntityManagerInterface::class);
        $this->relationType = new RelationType($this->personRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->relationType);
        unset($this->em);
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
