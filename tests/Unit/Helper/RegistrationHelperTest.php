<?php

namespace Tests\Unit\Helper;

use App\Controller\Helper\RegistrationHelper;
use App\Mail\MailService;
use App\Provider\Person\PersonRegistryInterface;
use Doctrine\ORM\EntityManager;
use Tests\Helper\AuthWebTestCase;
use Tests\Helper\Database\Activity\ActivityFixture;
use Tests\Helper\Database\Activity\PriceOptionFixture;

/**
 * Class RegistrationHelperTest.
 *
 * @covers \App\Controller\Helper\RegistrationHelper.php
 *
 * @author A-Daneel
 */
class RegistrationHelperTest extends AuthWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->login();

        $this->loadFixtures([
            PriceOptionFixture::class,
            ActivityFixture::class,
        ]);

        $mockPersonRegistry = $this->createMock(PersonRegistryInterface::class);
        $mockMailer = $this->createMock(MailService::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->helper = new RegistrationHelper($mockMailer, $mockPersonRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testCreateRegistrationNewForm(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function newAction(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function test(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testDeleteAction(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testCreateReserveNewForm(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testReserveNewAction(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testReserveMoveUpAction(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testReserveMoveDownAction(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
