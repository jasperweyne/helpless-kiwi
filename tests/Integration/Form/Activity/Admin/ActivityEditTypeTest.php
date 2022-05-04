<?php

namespace Tests\Integration\Form\Activity\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use App\Form\Activity\Admin\ActivityEditType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActivityEditTypeTest.
 *
 * @covers \App\Form\Activity\Admin\ActivityEditType
 */
class ActivityEditTypeTest extends KernelTestCase
{
    /**
     * @var ActivityEditType
     */
    protected $activityEditType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activityEditType = new ActivityEditType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityEditType);
    }

    public function testBindValidData()
    {
        $location = new Location();
        $location->setAddress('test');

        $type = new Activity();
        $formdata = [
            'name' => 'testname',
            'description' => 'test description',
            'location' => $location,
            'deadline' => 5,
            'start' => 10,
            'end' => 11,
            'capacity' => 50,
            'color' => 2,
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ActivityEditType::class, $type);

        $form->submit($formdata);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }

    public function testConfigureOptions(): void
    {
        /** @var MockObject&OptionsResolver */
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->exactly(1))->method('setDefaults');
        $this->activityEditType->configureOptions($resolver);
    }
}
