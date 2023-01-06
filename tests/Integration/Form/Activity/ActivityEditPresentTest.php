<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\ExternalRegistrant;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Location\Location;
use App\Entity\Order;
use App\Entity\Security\LocalAccount;
use App\Form\Activity\ActivityEditPresent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class ActivityEditPresentTest.
 *
 * @covers \App\Form\Activity\ActivityEditPresent
 */
class ActivityEditPresentTest extends KernelTestCase
{
    /**
     * @var ActivityEditPresent
     */
    protected $activityEditPresent;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
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
        // set initial values to satisfy validation constraints
        $type = (new Activity())
            ->setName('test')
            ->setDescription('test')
            ->setLocation(new Location())
            ->setDeadline(new \DateTime())
            ->setStart(new \DateTime())
            ->setEnd(new \DateTime('+5 minutes'))
            ->addOption(new PriceOption())
            ->setColor('test')
        ;

        $rDeleted = (new Registration())->setDeleteDate(new \DateTime());
        $rReserve = (new Registration())->setReservePosition(Order::create('a'));
        $rCurrent1 = (new Registration())->setPerson((new LocalAccount())->setName('b'));
        $rCurrent2 = (new Registration())->setPerson((new ExternalRegistrant())->setName('a'));

        $type->addRegistration($rDeleted);
        $type->addRegistration($rReserve);
        $type->addRegistration($rCurrent1);
        $type->addRegistration($rCurrent2);

        $formData = [
            'currentRegistrations' => [
                ['present' => 1], // sets $rCurrent2 to false
                ['present' => 2], // sets $rCurrent1 to true
            ],
        ];

        /** @var FormFactoryInterface */
        $formfactory = self::getContainer()->get('form.factory');
        $form = $formfactory->create(ActivityEditPresent::class, $type, ['csrf_protection' => false]);
        $view = $form->createView();
        $registrationView = $view['currentRegistrations'];

        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid(), $form->getErrors());
        self::assertNotNull($registrationView);
        self::assertEquals($registrationView->count(), 2);
        self::assertNotNull($registrationView[0]);
        self::assertNotNull($registrationView[1]);
        self::assertEquals('a', $registrationView[0]->vars['label']);
        self::assertEquals('b', $registrationView[1]->vars['label']);
        self::assertTrue($rCurrent1->getPresent());
        self::assertFalse($rCurrent2->getPresent());
    }
}
