<?php

namespace Tests\Unit\Entity\Security;

use App\Entity\Security\LocalAccount;
use DateTime;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

/**
 * Class LocalAccountTest.
 *
 * @covers \App\Entity\Security\LocalAccount
 */
class LocalAccountTest extends TestCase
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
        $expected = '42';
        $property = (new ReflectionClass(LocalAccount::class))
            ->getProperty('email');
        $property->setAccessible(true);
        $this->localAccount->setEmail($expected);
        $this->assertSame($expected, $property->getValue($this->localAccount));
    }

    public function testGetUsername(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetName(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetName(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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
        $expected = [];
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
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testEraseCredentials(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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
        $expected = Mockery::mock(DateTime::class);
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

    public function testIsPasswordRequestNonExpired(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testIsEqualTo(): void
    {
        /** @todo This test is incomplete. */
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
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetPasswordRequestSalt(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGetCanonical(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function test__toString(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
