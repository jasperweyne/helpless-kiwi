<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Registration;
use App\Form\Activity\PresentType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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

    public function testBindValidData(): void
    {
        $type = new Registration();
        $formData = [
            'present' => 2,
        ];

        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(PresentType::class, $type);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }

    public function testBuildForm(): void
    {
        $formbuildermock = $this->getMockBuilder("Symfony\Component\Form\Test\FormBuilderInterface")
            ->disableOriginalConstructor()
            ->getMock();
        $formbuildermock->expects(self::exactly(1))->method('addEventListener');
        $this->presentType->buildForm($formbuildermock, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects(self::exactly(1))->method('setDefaults');
        $this->presentType->configureOptions($resolver);
    }
}
