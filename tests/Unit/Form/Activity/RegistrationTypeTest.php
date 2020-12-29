<?php

namespace Tests\Unit\Form\Activity;

use App\Form\Activity\RegistrationType;
use App\Provider\Person\PersonRegistry;
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
        $this->registrationType = new RegistrationType($this->personRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationType);
        unset($this->personRegistry);
    }

    public function testBuildForm(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testConfigureOptions(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
