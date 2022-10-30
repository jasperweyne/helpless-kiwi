<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\ExternalRegistrant;
use App\Form\Activity\ExternalRegistrantType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ExternalRegistrantType.
 *
 * @covers \App\Form\Activity\ExternalRegistrantType
 */
class ExternalRegistrationTypeTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface&MockObject
     */
    protected $em;

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

        /** @var MockObject&EntityRepository<never> $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn([]);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->willReturn($repository);

        $this->externalRegistrationType = new ExternalRegistrantType($this->em);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->externalRegistrationType);
        unset($this->em);
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
