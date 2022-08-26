<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use App\Entity\Security\LocalAccount;
use App\Form\Activity\ActivityEditType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class ActivityEditTypeTest.
 *
 * @covers \App\Form\Activity\ActivityEditType
 */
class ActivityEditTypeTest extends KernelTestCase
{
    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $firewallName = 'main';

        $user = new LocalAccount();
        $token = new PostAuthenticationGuardToken($user, $firewallName, ['ROLE_USER']);

        /** @var TokenStorageInterface */
        $storage = self::$container->get(TokenStorageInterface::class);
        $storage->setToken($token);

        // Get all database tables
        $em = self::$container->get(EntityManagerInterface::class);
        $cmf = $em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($em);
        $schema->createSchema($classes);

        // Load database tool
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testBindValidData(): void
    {
        $location = new Location();
        $location->setAddress('test');

        $type = new Activity();
        $formdata = [
            'name' => 'testname',
            'description' => 'test description',
            'location' => $location,
            'deadline' => 5,
            'start' => 10,
            'end' => 11,
            'capacity' => 50,
            'color' => 2,
        ];

        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ActivityEditType::class, $type);

        $form->submit($formdata);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }

    public function testConfigureOptions(): void
    {
        /** @var MockObject&OptionsResolver */
        $resolver = $this->getMockBuilder("Symfony\Component\OptionsResolver\OptionsResolver")
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects(self::exactly(1))->method('setDefaults');
        self::$container->get(ActivityEditType::class)->configureOptions($resolver);
    }
}
