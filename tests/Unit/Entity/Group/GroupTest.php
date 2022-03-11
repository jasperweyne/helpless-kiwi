<?php

namespace Tests\Unit\Entity\Group;

use App\Entity\Group\Group;
use App\Entity\Group\Relation;
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
        $this->assertSame($expected, $this->group->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->group->setId($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getName());
    }

    public function testSetName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('name');
        $property->setAccessible(true);
        $this->group->setName($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getDescription());
    }

    public function testSetDescription(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(Group::class))
            ->getProperty('description');
        $property->setAccessible(true);
        $this->group->setDescription($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetParent(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('parent');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getParent());
    }

    public function testSetParent(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('parent');
        $property->setAccessible(true);
        $this->group->setParent($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetReadonly(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('readonly');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getReadonly());
    }

    public function testSetReadonly(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('readonly');
        $property->setAccessible(true);
        $this->group->setReadonly($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetRelationable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relationable');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getRelationable());
    }

    public function testSetRelationable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relationable');
        $property->setAccessible(true);
        $this->group->setRelationable($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetSubgroupable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('subgroupable');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getSubgroupable());
    }

    public function testSetSubgroupable(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('subgroupable');
        $property->setAccessible(true);
        $this->group->setSubgroupable($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetRelations(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getRelations());
    }

    public function testAddRelation(): void
    {
        $expected = new Relation();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $this->group->addRelation($expected);
        $this->assertSame($expected, $property->getValue($this->group)[0]);
    }

    public function testRemoveRelation(): void
    {
        $expected = new ArrayCollection();
        $relation = new Relation();
        $expected->add($relation);
        $property = (new ReflectionClass(Group::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($relation, $property->getValue($this->group)[0]);

        $this->group->removeRelation($relation);
        $this->assertNotSame($relation, $property->getValue($this->group));
    }

    public function testGetChildren(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('children');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testAddChild(): void
    {
        $expected = new Group();
        $property = (new ReflectionClass(Group::class))
            ->getProperty('children');
        $property->setAccessible(true);
        $this->group->addChild($expected);
        $this->assertSame($expected, $this->group->getChildren()[0]);
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
        $this->assertSame($group, $property->getValue($this->group)[0]);

        $this->group->removeChild($group);
        $this->assertNotSame($group, $property->getValue($this->group));
    }
    
    public function testIsActiveDefaultFalse(): void
    {
        $expected = false;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('active');
        $property->setAccessible(true);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testIsActive(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testSetActive(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('active');
        $property->setAccessible(true);
        $this->group->setActive($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }

    public function testGetRegister(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('register');
        $property->setAccessible(true);
        $property->setValue($this->group, $expected);
        $this->assertSame($expected, $this->group->getRegister());
    }

    public function testSetRegister(): void
    {
        $expected = true;
        $property = (new ReflectionClass(Group::class))
            ->getProperty('register');
        $property->setAccessible(true);
        $this->group->setRegister($expected);
        $this->assertSame($expected, $property->getValue($this->group));
    }
}
