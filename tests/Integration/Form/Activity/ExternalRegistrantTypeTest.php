<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\ExternalRegistrant;
use App\Form\Activity\ExternalRegistrantType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ExternalRegistrantType.
 *
 * @covers \App\Form\Activity\ExternalRegistrantType
 */
class ExternalRegistrationTypeTest extends KernelTestCase
{
    /**
     * @var ExternalRegistrantType
     */
    protected $externalRegistrationType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->externalRegistrationType = new ExternalRegistrantType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->externalRegistrationType);
    }

    public function testBindValidData(): void
    {
        $type = new ExternalRegistrant();
        $formData = [
            'name' => 'Chase',
            'email' => 'Chase@kiwi.nl'
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ExternalRegistrantType::class, $type);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this::exactly(1))->method('setDefaults');
        $this->externalRegistrationType->configureOptions($resolver);
    }
}
