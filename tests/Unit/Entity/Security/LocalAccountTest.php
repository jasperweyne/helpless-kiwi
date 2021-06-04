<?php

namespace Tests\Unit\Entity\Security;

use App\Entity\Security\LocalAccount;
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
        $this->assertSame($expected, $this->localAccount->getId());
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->localAccount->setId($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetEmail(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getEmail());
    }

    public function testSetEmail(): void
    {
        $expected = 'john@doe.eyes';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $this->localAccount->setEmail($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetUsername(): void
    {
        $expected = 'johndoe96';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getUsername());
    }

    public function testGetName(): void
    {
        $this->assertNull($this->localAccount->getName());
        $expectedJohn = 'John';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expectedJohn);
        $this->assertSame($expectedJohn, $this->localAccount->getName());

        $expectedDoe = 'Doe';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expectedDoe);

        $expectedResult = 'John Doe';
        $this->assertSame($expectedResult, $this->localAccount->getName());
    }

    public function testSetName(): void
    {
        $expected = 'Daneel';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $this->localAccount->setGivenName($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetGivenName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getGivenName());
    }

    public function testSetGivenName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('givenName');
        $property->setAccessible(true);
        $this->localAccount->setGivenName($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetFamilyName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getFamilyName());
    }

    public function testSetFamilyName(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('familyName');
        $property->setAccessible(true);
        $this->localAccount->setFamilyName($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetRoles(): void
    {
        $expected = ['ROLE_USER'];
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getRoles());
    }

    public function testSetRoles(): void
    {
        $expected = [];
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('roles');
        $property->setAccessible(true);
        $this->localAccount->setRoles($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetPassword(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getPassword());
    }

    public function testSetPassword(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('password');
        $property->setAccessible(true);
        $this->localAccount->setPassword($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetSalt(): void
    {
        //To-Do: implement or remove function
        $this->markTestIncomplete('Testing an empty function');
    }

    public function testEraseCredentials(): void
    {
        //To-Do: implement or remove function
        $this->markTestIncomplete('Testing an empty function');
    }

    public function testSetPasswordRequestToken(): void
    {
        $expected = null;
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestToken');
        $property->setAccessible(true);
        $this->localAccount->setPasswordRequestToken($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testSetPasswordRequestedAt(): void
    {
        $expected = new \DateTime;
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestedAt');
        $property->setAccessible(true);
        $this->localAccount->setPasswordRequestedAt($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetPasswordRequestedAt(): void
    {
        $expected = null;
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestedAt');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getPasswordRequestedAt());
    }

    /**
    * @depends testGetPasswordRequestedAt
    */
    public function testIsPasswordRequestNonExpired(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testIsEqualTo(): void
    {
        $this->markTestIncomplete();
    }

    public function testGetPasswordRequestToken(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('passwordRequestToken');
        $property->setAccessible(true);
        $property->setValue($this->localAccount, $expected);
        $this->assertSame($expected, $this->localAccount->getPasswordRequestToken());
    }

    public function testGetPasswordRequestSalt(): void
    {
        $this->markTestIncomplete('why again do we test unused functions? maybe remove');
    }

    public function testSetPasswordRequestSalt(): void
    {
        $this->markTestIncomplete('why again do we test unused functions? maybe remove');
    }

    public function testGetCanonical(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete('this actually needs testing');
    }

    public function test__toString(): void
    {
        $this->markTestIncomplete('do we need to test, it just regurgitates getCanonical');
    }
}

