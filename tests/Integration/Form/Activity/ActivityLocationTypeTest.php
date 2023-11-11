<?php

namespace Tests\Integration\Form\Location;

use App\Entity\Location\Location;
use App\Form\Location\LocationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActivityLocationTypeTest.
 *
 * @covers \App\Form\Activity\ActivityLocationType
 */
class ActivityLocationTypeTest extends KernelTestCase
{
    protected LocationType $activityLocationType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->activityLocationType = new LocationType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->activityLocationType);
    }

    public function testBuildForm(): void
    {
        $type = new Location();

        $formData = [
            'name' => 'here',
            'address' => 'there, obv',
        ];

        /** @var FormFactoryInterface $formfactory */
        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(LocationType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $this->activityLocationType->configureOptions($resolver);
        $opts = $resolver->resolve([]);

        self::assertSame($opts['data_class'], Location::class);
    }
}
