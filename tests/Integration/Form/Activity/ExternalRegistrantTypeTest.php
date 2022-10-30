<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\ExternalRegistrant;
use App\Form\Activity\ExternalRegistrantType;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ExternalRegistrantType.
 *
 * @covers \App\Form\Activity\ExternalRegistrantType
 */
class ExternalRegistrantTypeTest extends KernelTestCase
{
    /**
     * @var ExternalRegistrantType
     */
    protected $databaseTool;

    /**
     * @var ExternalRegistrantType
     */
    protected $externalRegistrantType;

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
        $this->externalRegistrantType = new ExternalRegistrantType($em);

        // Setup empty data
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class
        ]);
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
            'email' => LocalAccountFixture::USERNAME
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ExternalRegistrantType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this::exactly(1))->method('setDefaults');
        $this->externalRegistrantType->configureOptions($resolver);
    }
}
