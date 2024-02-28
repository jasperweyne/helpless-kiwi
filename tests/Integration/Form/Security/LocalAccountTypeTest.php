<?php

namespace Tests\Integration\Form\Security;

use App\Entity\Security\LocalAccount;
use App\Form\Security\LocalAccountType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LocalAccountTypeTest.
 *
 * @covers \App\Form\Security\LocalAccountType
 */
class LocalAccountTypeTest extends KernelTestCase
{
    protected LocalAccountType $localAccountType;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $this->localAccountType = new LocalAccountType();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->localAccountType);
    }

    public function testBuildForm(): void
    {
        $type = new LocalAccount();

        $formData = [
            'givenname' => 'John',
            'familyname' => 'Doe',
            'email' => 'john@doe.eye',
        ];

        /** @var FormFactoryInterface $formfactory */
        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(LocalAccountType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testConfigureOptions(): void
    {
        /** @var OptionsResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects(self::exactly(1))->method('setDefaults');
        $this->localAccountType->configureOptions($resolver);
    }
}
