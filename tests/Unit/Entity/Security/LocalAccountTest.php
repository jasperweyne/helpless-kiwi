<?php

namespace Tests\Unit\Entity\Security;

use App\Entity\Activity\Registration;
use App\Entity\Group\Group;
use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocalAccountTest.
 *
 * @covers \App\Entity\Security\LocalAccount
 */
class LocalAccountTest extends KernelTestCase
{
    /**
     * @var LocalAccount
     */
    protected $localAccount;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->localAccount = new LocalAccount();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->localAccount);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->localAccount->setId($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetEmail(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getEmail());
    }

    public function testSetEmail(): void
    {
        $expected = 'john@doe.eyes';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $this->localAccount->setEmail($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetUsername(): void
    {
        $expected = 'johndoe96';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getUsername());
    }

    public function testGetName(): void
    {
        self::assertNull($this->localAccount->getName());
        $expectedJohn = 'John';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expectedJohn);
        self::assertSame($expectedJohn, $this->localAccount->getName());

        $expectedDoe = 'Doe';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expectedDoe);

        $expectedResult = 'John Doe';
        self::assertSame($expectedResult, $this->localAccount->getName());
    }

    public function testSetName(): void
    {
        $expected = 'Daneel';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $this->localAccount->setName($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetGivenName(): void
    {
        $expected = 'John';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getGivenName());
    }

    public function testSetGivenName(): void
    {
        $expected = 'John';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $this->localAccount->setGivenName($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetFamilyName(): void
    {
        $expected = 'Doe';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getFamilyName());
    }

    public function testSetFamilyName(): void
    {
        $expected = 'Doe';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $this->localAccount->setFamilyName($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetRoles(): void
    {
        $group = new Group();
        $group->setActive(true);

        $expected = ['ROLE_USER'];
        $local = new ReflectionClass(LocalAccount::class);
        $roleProperty = $local
            ->getProperty('roles');
        $roleProperty->setAccessible(true);
        $roleProperty->setValue($this->localAccount, $expected);

        $relations = new ArrayCollection([$group]);
        $relationProperty = $local
            ->getProperty('relations');
        $relationProperty->setAccessible(true);
        $relationProperty->setValue($this->localAccount, $relations);

        $expectedroles = ['ROLE_USER', 'ROLE_AUTHOR'];
        self::assertEqualsCanonicalizing($expectedroles, $this->localAccount->getRoles());
    }

    public function testSetRoles(): void
    {
        $expected = [];
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('roles');
        $property->setAccessible(true);
        $this->localAccount->setRoles($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testIsAdmin(): void
    {
        $expected = ['ROLE_ADMIN'];
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertTrue($this->localAccount->isAdmin());
    }

    public function testGetPassword(): void
    {
        $expected = 'T0tallys3curePa$$w0rd';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getPassword());
    }

    public function testSetPassword(): void
    {
        $expected = 'T0tallys3curePa$$w0rd';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('password');
        $property->setAccessible(true);
        $this->localAccount->setPassword($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetSalt(): void
    {
        //To-Do: implement or remove function
        self::assertNull($this->localAccount->getSalt());
    }

    public function testEraseCredentials(): void
    {
        self::markTestIncomplete();
    }

    public function testGetOidc(): void
    {
        $expected = 'magicalSubjectClaim';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('oidc');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getOidc());
    }

    public function testSetOidc(): void
    {
        $expected = 'magicalSubjectClaim';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('oidc');
        $property->setAccessible(true);
        $this->localAccount->setOidc($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testSetPasswordRequestToken(): void
    {
        $expected = null;
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestToken');
        $property->setAccessible(true);
        $this->localAccount->setPasswordRequestToken($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testSetPasswordRequestedAt(): void
    {
        $expected = new \DateTime();
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestedAt');
        $property->setAccessible(true);
        $this->localAccount->setPasswordRequestedAt($expected);
        self::assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetPasswordRequestedAt(): void
    {
        $expected = null;
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestedAt');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getPasswordRequestedAt());
    }

    /**
     * @depends testGetPasswordRequestedAt
     */
    public function testIsPasswordRequestNonExpired(): void
    {
        $expected = new \DateTime();
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestedAt');
        $property->setAccessible(true);
        $this->localAccount->setPasswordRequestedAt($expected);
        self::assertTrue($this->localAccount->isPasswordRequestNonExpired(100));
    }

    public function testIsEqualTo(): void
    {
        $expected = 'john@doe.eyes';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $this->localAccount->setEmail($expected);
        self::assertTrue($this->localAccount->isEqualTo($this->localAccount));
    }

    public function testGetPasswordRequestToken(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestToken');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getPasswordRequestToken());
    }

    public function testGetPasswordRequestSalt(): void
    {
        self::assertNull($this->localAccount->getPasswordRequestSalt());
    }

    public function testSetPasswordRequestSalt(): void
    {
        self::assertSame($this->localAccount->setPasswordRequestSalt(), $this->localAccount);
    }

    public function testGetCanonical(): void
    {
        $id = '141592653589';
        $expectedPseudo = 'pseudonymized (14159265...)';
        $email = 'john@doe.eyes';
        $givenName = 'John';
        $familyName = 'Doe';
        $fullName = $givenName.' '.$familyName;

        //testing with just an id, no email or name
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $id);
        self::assertSame($expectedPseudo, $this->localAccount->getCanonical());

        //test with email
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $email);
        self::assertSame($email, $this->localAccount->getCanonical());

        //test with full name
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $givenName);

        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $familyName);
        self::assertSame($fullName, $this->localAccount->getCanonical());
    }

    public function testToString(): void
    {
        $id = '141592653589';
        $expectedPseudo = 'pseudonymized (14159265...)';
        $email = 'john@doe.eyes';
        $givenName = 'John';
        $familyName = 'Doe';
        $fullName = $givenName.' '.$familyName;

        //testing with just an id, no email or name
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $id);
        self::assertSame($expectedPseudo, $this->localAccount->__toString());

        //test with email
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $email);
        self::assertSame($email, $this->localAccount->__toString());

        //test with full name
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $givenName);

        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $familyName);
        self::assertSame($fullName, $this->localAccount->__toString());
    }

    public function testGetRegistrations(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getRegistrations());
    }

    public function testAddRegistration(): void
    {
        /** @var MockObject&Registration */
        $expected = $this->createMock(Registration::class);
        $expected->expects(self::once())->method('setPerson')->with($this->localAccount);
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('registrations');
        $property->setAccessible(true);
        $this->localAccount->addRegistration($expected);

        $collection = $property->getValue($this->localAccount);
        self::assertInstanceOf(Collection::class, $collection);
        self::assertContains($expected, $collection);
    }

    public function testRemoveRegistration(): void
    {
        /** @var MockObject&Registration */
        $expected = $this->createMock(Registration::class);
        $expected->method('getPerson')->willReturn($this->localAccount);
        $expected->expects(self::once())->method('setPerson')->with(null);
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('registrations');
        $property->setAccessible(true);

        $collection = $property->getValue($this->localAccount);
        self::assertInstanceOf(Collection::class, $collection);

        $collection->add($expected);
        $this->localAccount->removeRegistration($expected);
        self::assertNotContains($expected, $collection);
    }

    public function testGetRelations(): void
    {
        $expected = new ArrayCollection();
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        self::assertSame($expected, $this->localAccount->getRelations());
    }

    public function testAddRelation(): void
    {
        /** @var MockObject&Group */
        $expected = $this->createMock(Group::class);
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $this->localAccount->addRelation($expected);
        $collection = $property->getValue($this->localAccount);
        self::assertInstanceOf(Collection::class, $collection);
        self::assertContains($expected, $collection);
    }

    public function testRemoveRelation(): void
    {
        /** @var MockObject&Group */
        $expected = $this->createMock(Group::class);
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('relations');
        $property->setAccessible(true);
        $collection = $property->getValue($this->localAccount);
        self::assertInstanceOf(Collection::class, $collection);

        $collection->add($expected);
        $this->localAccount->removeRelation($expected);
        self::assertNotContains($expected, $collection);
    }

    public function testGetActivityGroups(): void
    {
        $group = new Group();
        $group->setActive(true);

        $local = new ReflectionClass(LocalAccount::class);

        $relations = new ArrayCollection([$group]);
        $relationProperty = $local
            ->getProperty('relations');
        $relationProperty->setAccessible(true);
        $relationProperty->setValue($this->localAccount, $relations);

        $expectedroles = ['ROLE_USER', 'ROLE_AUTHOR'];
        self::assertSame([$group], $this->localAccount->getActiveGroups());
    }
}
