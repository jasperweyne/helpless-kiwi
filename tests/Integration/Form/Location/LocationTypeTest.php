<?php

namespace Tests\Integration\Form\Location;

use App\Form\Location\LocationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class LocationTypeTest.
 *
 * @covers \App\Form\Location\LocationType
 */
class LocationTypeTest extends KernelTestCase
{
    /**
     * @var LocationType
     */
    protected $locationType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->locationType = new LocationType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->locationType);
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
