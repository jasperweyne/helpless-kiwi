<?php

namespace Tests\Unit\Security;

use App\Provider\Person\Person;
use App\Security\OAuth2User;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OAuth2UserTest.
 *
 * @covers \App\Security\OAuth2User
 */
class OAuth2UserTest extends KernelTestCase
{
    /**
     * @var OAuth2User
     */
    protected $oAuth2User;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @todo Correctly instantiate tested object to use it. */
        $this->oAuth2User = new OAuth2User();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->oAuth2User);
    }

    public function testGetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(OAuth2User::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->oAuth2User, $expected);
        $this->assertSame($expected, $this->oAuth2User->getId());
    }

    public function testGetUsername(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testSetId(): void
    {
        $expected = '42';
        $property = (new ReflectionClass(OAuth2User::class))
            ->getProperty('id');
        $property->setAccessible(true);
        $this->oAuth2User->setId($expected);
        $this->assertSame($expected, $property->getValue($this->oAuth2User));
    }

    public function testGetRoles(): void
    {
        $expected = ['ROLE_USER'];
        $property = (new ReflectionClass(OAuth2User::class))
            ->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($this->oAuth2User, $expected);
        $this->assertSame($expected, $this->oAuth2User->getRoles());
    }

    public function testSetRoles(): void
    {
        $expected = [];
        $property = (new ReflectionClass(OAuth2User::class))
            ->getProperty('roles');
        $property->setAccessible(true);
        $this->oAuth2User->setRoles($expected);
        $this->assertSame($expected, $property->getValue($this->oAuth2User));
    }

    public function testGetPerson(): void
    {
        $expected = new Person();
        $property = (new ReflectionClass(OAuth2User::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $property->setValue($this->oAuth2User, $expected);
        $this->assertSame($expected, $this->oAuth2User->getPerson());
    }

    public function testSetPerson(): void
    {
        $expected = new Person();
        $property = (new ReflectionClass(OAuth2User::class))
            ->getProperty('person');
        $property->setAccessible(true);
        $this->oAuth2User->setPerson($expected);
        $this->assertSame($expected, $property->getValue($this->oAuth2User));
    }

    public function testGetPassword(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
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

    public function testIsEqualTo(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
