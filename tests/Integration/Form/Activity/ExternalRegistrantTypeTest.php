<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\ExternalRegistrant;
use App\Form\Activity\ExternalRegistrantType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ExternalRegistrantType.
 *
 * @covers \App\Form\Activity\ExternalRegistrantType
 */
class ExternalRegistrantTypeTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @var ExternalRegistrantType
     */
    protected $externalRegistrantType;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->externalRegistrantType = new ExternalRegistrantType($em);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->externalRegistrantType);
        unset($this->em);
    }

    public function testBindValidData(): void
    {
        $type = new ExternalRegistrant();
        $formData = [
            'name' => 'Chase',
            'email' => 'Chase@kiwi.nl',
        ];

        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(ExternalRegistrantType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testBindDuplicateData(): void
    {
        $type = new ExternalRegistrant();
        $formData = [
            'name' => 'Chase',
            'email' => 'admin@kiwi.nl',
        ];

        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(ExternalRegistrantType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
    }

    public function testConfigureOptions(): void
    {
        /** @var MockObject&OptionsResolver */
        $resolver = $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this::exactly(1))->method('setDefaults');
        $this->externalRegistrantType->configureOptions($resolver);
    }
}
