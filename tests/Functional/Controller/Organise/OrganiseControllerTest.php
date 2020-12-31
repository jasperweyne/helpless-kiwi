<?php

namespace Tests\Functional\Controller\Organise;

use App\Controller\Organise\OrganiseController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class OrganiseControllerTest.
 *
 * @covers \App\Controller\Organise\OrganiseController
 */
class OrganiseControllerTest extends WebTestCase
{
    /**
     * @var OrganiseController
     */
    protected $organiseController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @todo Correctly instantiate tested object to use it. */
        $this->organiseController = new OrganiseController();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->organiseController);
    }

    public function testIndexAction(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
