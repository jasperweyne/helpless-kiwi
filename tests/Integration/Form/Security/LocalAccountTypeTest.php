<?php

namespace Tests\Integration\Form\Security;

use App\Entity\Security\LocalAccount;
use App\Form\Security\LocalAccountType;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
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
    protected AbstractDatabaseTool $databaseTool;
    protected EntityManagerInterface $em;
    protected LocalAccountType $localAccountType;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        // Get all database tables
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $cmf = $this->em->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        // Write all tables to database
        $schema = new SchemaTool($this->em);
        $schema->createSchema($classes);

        // Load database tool
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->localAccountType = new LocalAccountType();

        // Setup empty data
        $this->databaseTool->loadFixtures([
            LocalAccountFixture::class,
        ]);
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
