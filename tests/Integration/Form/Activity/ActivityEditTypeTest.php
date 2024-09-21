<?php

namespace Tests\Integration\Form\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Location\Location;
use App\Entity\Security\LocalAccount;
use App\Form\Activity\ActivityEditType;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ActivityEditTypeTest.
 *
 * @covers \App\Form\Activity\ActivityEditType
 */
class ActivityEditTypeTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $firewallName = 'main';

        $user = new LocalAccount();
        $token = new TestBrowserToken(['ROLE_USER'], $user, $firewallName);

        /** @var TokenStorageInterface */
        $storage = self::getContainer()->get(TokenStorageInterface::class);
        $storage->setToken($token);
    }

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

        $formfactory = self::getContainer()->get('form.factory');
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
        self::getContainer()->get(ActivityEditType::class)->configureOptions($resolver);
    }
}
