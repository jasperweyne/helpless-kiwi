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

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->registrationedittype = new RegistrationEditType();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->registrationedittype);
    }

    public function testBindValidData(): void
    {
        $type = new Registration();
        $formData = [
            'comment' => 'test comment',
            'transferable' => 'Ja',
        ];

        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(RegistrationEditType::class, $type);

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
    }
}
