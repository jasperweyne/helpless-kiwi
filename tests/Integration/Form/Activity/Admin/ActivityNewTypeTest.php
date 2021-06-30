<?php

namespace Tests\Integration\Form\Activity\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use App\Form\Activity\Admin\ActivityNewType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ActivityNewTypeTest.
 *
 * @covers \App\Form\Activity\Admin\ActivityNewType
 */
class ActivityNewTypeTest extends KernelTestCase
{
    /**
     * @var ActivityNewType
     */
    protected $activityNewType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activityNewType = new ActivityNewType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityNewType);
    }

    public function testBindValidData()
    {
        $location = new Location();
        $location->setAddress('test');
        $local_file = __DIR__.'/../../../../assets/Faint.png';

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
            'imageFile' => new UploadedFile(
                $local_file,
                'Faint.png',
                'image/png',
                null,
                null,
                true
            ),
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ActivityNewType::class, $type);

        $form->submit($formdata);
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->exactly(1))->method('setDefaults');
        $this->activityNewType->configureOptions($resolver);
    }
}
