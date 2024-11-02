<?php

namespace Tests\Integration\Form\Location;

use App\Entity\Location\Location;
use App\Form\Delete\LocationDeleteData;
use App\Form\Location\LocationDeleteType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LocationDeleteTypeTest.
 *
 * @covers \App\Form\Location\LocationDeleteType
 */
class LocationDeleteTypeTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @var EntityRepository<Location>
     */
    protected $repository;

    /**
     * @var LocationDeleteType
     */
    protected $locationDeleteType;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /* @todo Correctly instantiate tested object to use it. */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->locationDeleteType = new LocationDeleteType();
        $this->repository = $em->getRepository(Location::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->locationType);
        unset($this->databaseTool);
    }

    public function testBindValidData(): void
    {
        $type = new LocationDeleteData();
        $location = $this->repository->findOneBy(['address' => '@localhost']);
        $replaced = $this->repository->findOneBy(['address' => '@externalhost']);
        self::assertNotNull($replaced);

        $formfactory = self::getContainer()->get('form.factory');
        /** @var FormInterface $form */
        $form = $formfactory->create(LocationDeleteType::class, $type, [
            'csrf_protection' => false,
            'location' => $location,
        ]);

        $form->submit([
            'activity' => $replaced->getId(),
        ]);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid(), strval($form->getErrors(true)));
    }

    public function testConfigureOptions(): void
    {
        /** @var OptionsResolver&MockObject */
        $resolver = $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this::exactly(1))->method('setDefaults');
        $this->locationDeleteType->configureOptions($resolver);
    }
}
