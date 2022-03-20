<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\PriceOption;
use App\Form\Activity\PriceOptionType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PriceOptionTypeTest.
 *
 * @covers \App\Form\Activity\PriceOptionType
 */
class PriceOptionTypeTest extends KernelTestCase
{
    /**
     * @var PriceOptionType
     */
    protected $priceOptionType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->priceOptionType = new PriceOptionType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->priceOptionType);
    }

    public function testBindValidData(): void
    {
        $type = new PriceOption();
        $formdata = [
            'name' => 'testname',
            'price' => 300,
            'target' => null,
        ];

        /** @var FormFactoryInterface $formfactory */
        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(PriceOptionType::class, $type);

        $form->submit($formdata);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }

    public function testConfigureOptions(): void
    {
        /** @var OptionsResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects(self::exactly(1))->method('setDefaults');
        $this->priceOptionType->configureOptions($resolver);
    }
}
