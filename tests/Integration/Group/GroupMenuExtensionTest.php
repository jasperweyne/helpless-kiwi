<?php

namespace Tests\Integration\Group;

use App\Entity\Security\LocalAccount;
use App\Group\GroupMenuExtension;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class GroupMenuExtensionTest.
 *
 * @covers \App\Group\GroupMenuExtension
 */
class GroupMenuExtensionTest extends KernelTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @var GroupMenuExtension
     */
    protected $groupMenuExtension;

    /**
     * @var LocalAccount
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        // get user
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $users = $em->getRepository(LocalAccount::class)->findAll();
        assert(isset($users[0]));
        $this->user = $users[0];

        // build token storage
        $token = new TestBrowserToken(['ROLE_USER'], $this->user, 'main');
        $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);
        $tokenStorage->setToken($token);

        $this->groupMenuExtension = new GroupMenuExtension($tokenStorage);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->groupMenuExtension);
        unset($this->user);
    }

    public function testGetMenuItems(): void
    {
        $expected = [];
        self::assertSame($expected, $this->groupMenuExtension->getMenuItems());
    }

    public function testGetAdminMenuItems(): void
    {
        $expected = count($this->user->getActiveGroups());
        $result = $this->groupMenuExtension->getMenuItems('admin');
        self::assertCount(1, $result);
        self::assertSame('Activiteiten', $result[0]['title']);
        self::assertNotEquals(0, $expected);
        self::assertArrayHasKey('sub', $result[0]);
        self::assertCount($expected, $result[0]['sub']);
    }
}
