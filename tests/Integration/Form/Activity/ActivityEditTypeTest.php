<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Activity;
use App\Form\Activity\ActivityEditType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ActivityEditTypeTest.
 *
 * @covers \App\Form\Activity\ActivityEditType
 */
class ActivityEditTypeTest extends KernelTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testBuildForm(): void
    {
        $type = new Activity();
        $formData = [
            'name' => 'test name',
            'description' => 'test description',
            'deadline' => new DateTime(),
            'start' => new DateTime(),
            'end' => new DateTime(),
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ActivityEditType::class, $type);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }
}
