<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Mail\Mail;
use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MailControllerTest.
 *
 * @covers \App\Controller\Admin\MailController
 */
class MailControllerTest extends AuthWebTestCase
{
    protected EntityManagerInterface $em;

    private string $controllerEndpoint = '/admin/mail';

    protected function setUp(): void
    {
        parent::setUp();

        $this->login();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testIndexAction(): void
    {
        $this->client->request('GET', $this->controllerEndpoint);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('#title', 'Mails');
    }

    public function testShowAction(): void
    {
        // Arrange
        $mail = $this->em->getRepository(Mail::class)->findAll()[0];
        $title = $mail->getTitle();
        $id = $mail->getId();

        // Act
        $this->client->request('GET', $this->controllerEndpoint."/{$id}/");

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertIsString($title);
        self::assertSelectorTextContains('#title', $title);
    }
}
