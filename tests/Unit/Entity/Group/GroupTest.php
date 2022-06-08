<?php

namespace Tests\Unit\Entity\Group;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class GroupTest.
 *
 * @covers \App\Entity\Group\Group
 * @group entities
 */
class GroupTest extends KernelTestCase
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->group = new Group();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->group);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertSame($expected, $this->group->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->group->setId($expected);
        self::assertSame($expected, $property->getValue($this->group));
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertSame($expected, $this->group->getName());
    }

    public function testSetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $this->group->setName($expected);
        self::assertSame($expected, $property->getValue($this->group));
    }

    public function testGetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertSame($expected, $this->group->getDescription());
    }

    public function testSetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $this->group->setDescription($expected);
        self::assertSame($expected, $property->getValue($this->group));
    }

    public function testGetParent(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('parent');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertSame($expected, $this->group->getParent());
    }

    public function testSetParent(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('parent');
        $property->setAccessible(true);
        $this->group->setParent($expected);
        self::assertSame($expected, $property->getValue($this->group));
    }

    public function testGetReadonly(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('readonly');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertTrue($this->group->getReadonly());
    }

    public function testSetReadonly(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('readonly');
        $property->setAccessible(true);
        $this->group->setReadonly($expected);
        self::assertTrue($property->getValue($this->group));
    }

    public function testGetRelationable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relationable');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertTrue($this->group->getRelationable());
    }

    public function testSetRelationable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relationable');
        $property->setAccessible(true);
        $this->group->setRelationable($expected);
        self::assertTrue($property->getValue($this->group));
    }

    public function testGetSubgroupable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('subgroupable');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertTrue($this->group->getSubgroupable());
    }

    public function testSetSubgroupable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('subgroupable');
        $property->setAccessible(true);
        $this->group->setSubgroupable($expected);
        self::assertTrue($property->getValue($this->group));
    }

    public function testGetRelations(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertSame($expected, $this->group->getRelations());
    }

    public function testGetAllRelationFor(): void
    {
        //Assign
        $user = new LocalAccount();
        $group = new Group();
        $group2 = new Group();
        $relation = new Relation();
        $relation2 = new Relation();

        $this->group->setParent($group);
        $group->setParent($group2);

        $relation->setGroup($group);
        $relation->setPerson($user);

        $relation2->setGroup($group2);
        $relation2->setPerson($user);

        $user->addRelation($relation);
        $user->addRelation($relation2);
        $group->addRelation($relation);
        $group2->addRelation($relation2);

        $expected = new ArrayCollection();
        $expected->add($relation);
        $expected->add($relation2);

        //Assert
        self::assertCount(count($expected), $this->group->getAllRelationFor($user));
    }

    public function testAddRelation(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testRemoveRelation(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testGetChildren(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('children');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertSame($expected, $this->group->getChildren());
    }

    public function testAddChild(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('children');
        $property->setAccessible(true);
        $this->group->addChild($expected);
        self::assertSame($expected, $this->group->getChildren()[0]);
    }

    public function testRemoveChild(): void
    {
        $expected = new ArrayCollection();
        $group = new Group();
        $expected->add($group);
        $property = (new ReflectionClass(Group::class))
            ->getProperty('children');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);

        $this->group->removeChild($group);
        self::assertNotSame($group, $property->getValue($this->group));
    }

    public function testIsActiveDefaultFalse(): void
    {
        $property = (new ReflectionClass(Group::class))
            ->getProperty('active');
        $property->setAccessible(true);
        self::assertFalse($property->getValue($this->group));
    }

    public function testIsActive(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertTrue($property->getValue($this->group));
    }

    public function testSetActive(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('active');
        $property->setAccessible(true);
        $this->group->setActive($expected);
        self::assertTrue($property->getValue($this->group));
    }

    public function testGetRegister(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('register');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        self::assertTrue($this->group->getRegister());
    }

    public function testSetRegister(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('register');
        $property->setAccessible(true);
        $this->group->setRegister($expected);
        self::assertTrue($property->getValue($this->group));
    }
}
