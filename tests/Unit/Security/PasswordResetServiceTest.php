<?php

namespace Tests\Unit\Security;

use App\Entity\Security\LocalAccount;
use App\Security\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Class PasswordResetServiceTest.
 *
 * @covers \App\Security\PasswordResetService
 */
class PasswordResetServiceTest extends KernelTestCase
{
    /**
     * @var PasswordResetService
     */
    protected $passwordResetService;

    /**
     * @var EntityManagerInterface&MockObject
     */
    protected $em;

    /**
     * @var PasswordHasherFactoryInterface&MockObject
     */
    protected $encoderFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->encoderFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $this->passwordResetService = new PasswordResetService($this->em, $this->encoderFactory);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->passwordResetService);
        unset($this->em);
        unset($this->encoderFactory);
    }

    public function testIsPasswordRequestTokenValid(): void
    {
        // Arrange data
        $token = 'abc123';
        $auth = new LocalAccount();
        $auth
            ->setPasswordRequestToken($token)
            ->setPasswordRequestedAt(new \DateTime('-10 minutes'))
        ;

        // Arrange stubs
        /** @var PasswordHasherInterface&MockObject */
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher->method('verify')->willReturnCallback(fn ($expected, $token) => $expected === $token);

        $this->encoderFactory->method('getPasswordHasher')->willReturn($hasher);

        // Act
        $result = $this->passwordResetService->isPasswordRequestTokenValid($auth, $token);

        // Assert
        self::assertTrue($result);
    }

    public function testGeneratePasswordRequestToken(): void
    {
        // Arrange data
        $auth = new LocalAccount();

        // Arrange stubs
        /** @var PasswordHasherInterface&MockObject */
        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher->method('hash')->will(self::returnArgument(0));

        $this->encoderFactory->method('getPasswordHasher')->willReturn($hasher);

        // Expect the object to be flushed to db
        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        // Act
        $token = $this->passwordResetService->generatePasswordRequestToken($auth);

        // Assert
        self::assertEquals($token, $auth->getPasswordRequestToken());
        self::assertNotNull($auth->getPasswordRequestedAt());
    }

    public function testResetPasswordRequestToken(): void
    {
        // Arrange
        $auth = new LocalAccount();
        $auth
            ->setPasswordRequestToken('abc')
            ->setPasswordRequestedAt(new \DateTime())
        ;

        // Expect the object to be flushed to db
        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        // Act
        $this->passwordResetService->resetPasswordRequestToken($auth);

        // Assert
        self::assertNull($auth->getPasswordRequestedAt());
        self::assertNull($auth->getPasswordRequestToken());
    }
}
