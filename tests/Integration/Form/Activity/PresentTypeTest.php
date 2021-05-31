<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Registration;
use App\Form\Activity\PresentType;
use App\Provider\Person\PersonRegistry;
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
     * @var PersonRegistry
     */
    protected $personRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->personRegistry = self::$container->get(PersonRegistry::class);
        $this->presentType = new PresentType($this->personRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->presentType);
        unset($this->personRegistry);
    }

    public function testBindValidData()
    {
        $type = new Registration();
        $formData = [
            'present' => 2,
            'comment' => 'This is a test comment for testing purposes',
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(PresentType::class, $type);

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
    }

    public function testBuildForm(): void
    {
        $formbuildermock = $this->getMockBuilder("Symfony\Component\Form\Test\FormBuilderInterface")
            ->disableOriginalConstructor()
            ->getMock();
        $formbuildermock->expects($this->exactly(1))->method('addEventListener');
        $this->presentType->buildForm($formbuildermock, []);
    }

    public function testConfigureOptions(): void
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->exactly(1))->method('setDefaults');
        $this->presentType->configureOptions($resolver);
    }
}
