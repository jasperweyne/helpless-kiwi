<?php

namespace Tests\Integration\Form\Activity\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use App\Entity\Security\LocalAccount;
use App\Form\Activity\Admin\ActivityNewType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class ActivityNewTypeTest.
 *
 * @covers \App\Form\Activity\Admin\ActivityNewType
 */
class ActivityNewTypeTest extends KernelTestCase
{
    use FixturesTrait;

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
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testBindValidData()
    {
        $location = new Location();
        $location->setAddress('test');
        $local_file = __DIR__.'/../../../../assets/Faint.png';

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
            'imageFile' => new UploadedFile(
                $local_file,
                'Faint.png',
                'image/png',
                null,
                null,
                true
            ),
        ];

        /** @var FormFactoryInterface */
        $formfactory = self::$container->get('form.factory');
        $form = $formfactory->create(ActivityNewType::class, $type);

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
        $resolver->expects($this->exactly(1))->method('setDefaults');
        self::$container->get(ActivityNewType::class)->configureOptions($resolver);
    }
}
