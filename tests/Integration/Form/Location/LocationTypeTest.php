<?php

namespace Tests\Integration\Form\Location;

use App\Entity\Location\Location;
use App\Form\Location\LocationType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LocationTypeTest.
 *
 * @covers \App\Form\Location\LocationType
 */
class LocationTypeTest extends KernelTestCase
{
    protected LocationType $locationType;

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
        $this->locationType->configureOptions($resolver);
        $opts = $resolver->resolve([]);

        self::assertSame($opts['data_class'], Location::class);
    }
}
