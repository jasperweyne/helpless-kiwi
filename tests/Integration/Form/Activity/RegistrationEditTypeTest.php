<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Registration;
use App\Form\Activity\RegistrationEditType;
use App\Form\Activity\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class RegistrationTypeTest.
 *
 * @covers \App\Form\Activity\RegistrationEditType
 */
class RegistrationEditTypeTest extends KernelTestCase
{
    /**
     * @var RegistrationType
     */
    protected $registrationType;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var RegistrationEditType
     */
    protected $registrationedittype;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registerationedittype = new RegistrationEditType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationedittype);
    }

    public function testBindValidData()
    {
        $type = new Registration();
        $formData = [
            'comment' => 'test comment',
        ];

        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(RegistrationEditType::class, $type);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }
}
