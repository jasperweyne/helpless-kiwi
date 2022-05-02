<?php

namespace Tests\Integration\Form\Activity;

use App\Form\Activity\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationTypeTest.
 *
 * @covers \App\Form\Activity\RegistrationType
 */
class RegistrationTypeTest extends KernelTestCase
{
    /**
     * @var RegistrationType
     */
    protected $registrationType;

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
        $this->registrationType = new RegistrationType($this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationType);
        unset($this->em);
    }

    public function testBuildForm(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /* @todo This test is incomplete. */
        self::markTestIncomplete();
    }
}
