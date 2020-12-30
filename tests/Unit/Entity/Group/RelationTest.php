<?php

namespace Tests\Unit\Entity\Group;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RelationTest.
 *
 * @covers \App\Entity\Group\Relation
 */
class RelationTest extends KernelTestCase
{
    /**
     * @var Relation
     */
    protected $relation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->relation = new Relation();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->relation);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->relation, $expected);
        $this->assertSame($expected, $this->relation->getId());
    }

    public function testGetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $property->setValue($this->relation, $expected);
        $this->assertSame($expected, $this->relation->getDescription());
    }

    public function testSetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $this->relation->setDescription($expected);
        $this->assertSame($expected, $property->getValue($this->relation));
    }

    public function testGetGroup(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('group');
        $property->setAccessible(true);
        $property->setValue($this->relation, $expected);
        $this->assertSame($expected, $this->relation->getGroup());
    }

    public function testSetGroup(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('group');
        $property->setAccessible(true);
        $this->relation->setGroup($expected);
        $this->assertSame($expected, $property->getValue($this->relation));
    }

    public function testGetPersonId(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetPersonId(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetParent(): void
    {
        $expected = new Relation();
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('parent');
        $property->setAccessible(true);
        $property->setValue($this->relation, $expected);
        $this->assertSame($expected, $this->relation->getParent());
    }

    public function testSetParent(): void
    {
        $expected = new Relation();
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('parent');
        $property->setAccessible(true);
        $this->relation->setParent($expected);
        $this->assertSame($expected, $property->getValue($this->relation));
    }

    public function testGetChildren(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Relation::class))
            ->getProperty('children');
        $property->setAccessible(true);
        $property->setValue($this->relation, $expected);
        $this->assertSame($expected, $this->relation->getChildren());
    }

    public function testAddChild(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testRemoveChild(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetRoot(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetChildrenRecursive(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetAllRelations(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}