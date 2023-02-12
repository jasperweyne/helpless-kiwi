<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Form\Activity\PresentType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class PresentTypeTest.
 *
 * @covers \App\Form\Activity\PresentType
 */
class PresentTypeTest extends KernelTestCase
{
    /**
     * @var PresentType
     */
    protected $presentType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->presentType = new PresentType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->presentType);
    }

    public function testBindValidData()
    {
        $type = new Registration();
        $type->setOption(new PriceOption());

        $formData = [
            'present' => 2,
        ];

        /** @var FormFactoryInterface */
        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(PresentType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->exactly(1))->method('setDefaults');
        $this->presentType->configureOptions($resolver);
    }
}
