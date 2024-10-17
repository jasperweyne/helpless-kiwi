<?php

namespace Tests\Integration\Form\Security;

use App\Entity\Security\LocalAccount;
use App\Form\Security\LocalAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
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
    use RecreateDatabaseTrait;

    protected EntityManagerInterface $em;
    protected LocalAccountType $localAccountType;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
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

    public function testUniqueEmailConstraint(): void
    {
        $uniqueAccount = new LocalAccount();
        $duplicateAccount = new LocalAccount();

        $formData = [
            'givenname' => 'John',
            'familyname' => 'Doe',
            'email' => 'john@doe.eye',
        ];

        /** @var FormFactoryInterface $formfactory */
        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(LocalAccountType::class, $uniqueAccount, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
        $this->em->persist($uniqueAccount);
        $this->em->flush();

        $form2 = $formfactory->create(LocalAccountType::class, $duplicateAccount, ['csrf_protection' => false]);
        $form2->submit($formData);
        $errors = $form2->getErrors(true);
        self::assertFalse($form2->isValid());
        self::assertEquals('This e-mail address is already in use.', $errors->current()->getMessage());
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
