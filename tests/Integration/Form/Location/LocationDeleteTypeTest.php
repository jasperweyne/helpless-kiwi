<?php

namespace Tests\Integration\Form\Location;

use App\Entity\Location\Location;
use App\Form\Delete\LocationDeleteData;
use App\Form\Location\LocationDeleteType;
use App\Tests\Database\Location\LocationFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
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
    /**
     * @var EntityRepository<Location>
     */
    protected $repository;

    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;

    /**
     * @var LocationDeleteType
     */
    protected $locationDeleteType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        // Get all database tables
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        // Load database tool
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        // Setup empty data
        $this->databaseTool->loadFixtures([
            LocationFixture::class
        ]);

        /* @todo Correctly instantiate tested object to use it. */
        $this->locationDeleteType = new LocationDeleteType();
        $this->repository = $em->getRepository(Location::class);
    }

    /**
     * {@inheritdoc}
     */
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
