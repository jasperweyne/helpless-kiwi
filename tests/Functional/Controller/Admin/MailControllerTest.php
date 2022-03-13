<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Mail\Mail;
use App\Tests\AuthWebTestCase;
use App\Tests\Database\Mail\MailFixture;
use App\Tests\Database\Security\LocalAccountFixture;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class MailControllerTest.
 *
 * @covers \App\Controller\Admin\MailController
 */
class MailControllerTest extends AuthWebTestCase
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    private $controllerEndpoint = "/admin/mail";

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LocalAccountFixture::class,
            MailFixture::class,
        ]);

        $this->login();
        $this->em = self::$container->get(EntityManagerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->em);
    }

    public function testIndexAction(): void
    {
        $this->client->request('GET', $this->controllerEndpoint . "/");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('span', 'Mails');
    }

    public function testShowAction(): void
    {
        // Arrange
        $mail = $this->em->getRepository(Mail::class)->findAll()[0];
        $title = $mail->getTitle();
        $id = $mail->getId();

        // Act
        $this->client->request('GET', "/admin/mail/{$id}");

        // Assert
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('span', $title);
    }
}
