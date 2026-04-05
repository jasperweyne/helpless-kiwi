<?php

namespace Tests\Functional\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use App\Tests\AuthWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class RegistrationControllerTest.
 *
 * @covers \App\Controller\Admin\RegistrationController
 *
 * @author A-Daneel
 */
class RegistrationControllerTest extends AuthWebTestCase
{
    private EntityManagerInterface $em;

    private string $controller = '/admin/activity/register';

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

    public function testNewExternalActionGet(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', $this->controller."/new/{$id}/external");

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorExists('form');
        self::assertSelectorExists('#registration');
        self::assertPageTitleContains('Nieuwe aanmelding voor');
        self::assertSelectorTextContains('#registration_person > div:nth-child(1) > label', 'Name');
        self::assertSelectorTextContains('#registration_person > div:nth-child(2) > label', 'Email');
        self::assertSelectorTextContains('#registration > div:nth-child(2) > label', 'Optie');
        self::assertSelectorTextContains('#registration > div:nth-child(3) > label', 'Comment');
    }

    public function testNewActionGet(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $id = $activity->getId();

        // Act
        $this->client->request('GET', $this->controller."/new/{$id}");

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testNewActionGet
     */
    public function testNewActionPost(): void
    {
        // Arrange
        $activity = $this->em->getRepository(Activity::class)->findAll()[0];
        $originalCount = $activity->getRegistrations()->count();
        $id = $activity->getId();

        // Act
        $this->client->request('GET', $this->controller."/new/{$id}/");
        $this->client->submitForm('Toevoegen');

        // Assert
        $activity = $this->em->getRepository(Activity::class)->find($id);
        self::assertNotNull($activity);
        $newCount = $activity->getRegistrations()->count();
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'gelukt');
        self::assertEquals(1, $newCount - $originalCount, "Registration count of activity didn't correctly change after POST request.");
    }

    public function testEditActionPost(): void
    {
        // Arrange
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $id = $registration->getId();
        $crawler = $this->client->request('GET', $this->controller."/edit/{$id}");
        $comment = 'This is a test comment';

        // Act
        $form = $crawler->selectButton('Verander')->form();
        $form['registration_edit[comment]'] = $comment;
        $this->client->submit($form);

        // Assert
        $currentcomment = $this->em->getRepository(Registration::class)->find($id);
        self::assertNotNull($currentcomment);
        $newcomment = $currentcomment->getComment();

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertEquals($comment, $newcomment);
    }

    public function testDeleteActionGet(): void
    {
        // Arrange
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $id = $registration->getId();

        // Act
        $this->client->request('GET', $this->controller."/delete/{$id}");

        // Assert
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *  @depends testDeleteActionGet
     */
    public function testDeleteActionPost(): void
    {
        // Arrange
        $registration = $this->em->getRepository(Registration::class)->findAll()[0];
        $id = $registration->getId();
        self::assertEquals(null, $registration->getDeleteDate());

        // Act
        $crawler = $this->client->request('GET', $this->controller."/delete/{$id}");
        $form = $crawler->selectButton('Ja, meld af')->form();
        $this->client->submit($form);

        // Assert
        $registration = $this->em->getRepository(Registration::class)->find($id);
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSelectorTextContains('.container', 'gelukt');
        self::assertNotNull($registration);
        self::assertNotNull($registration->getDeleteDate());
    }

    /**
     * @dataProvider noAccessProvider
     */
    public function testNoAccess(string $url): void
    {
        // arrange
        /** @var Activity $activity */
        $activity = $this->em->getRepository(Activity::class)->findOneBy(['name' => 'activity_1']);

        /** @var Registration $registration */
        $registration = $activity->getRegistrations()[0];
        $id = $registration->getId();
        $url = str_replace('id', strval($id), $url);

        // act
        $this->logout();
        $this->login([]);
        $this->client->request('GET', $url);

        // assert
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider noAccessProvider
     */
    public function testNullActivityNotAdmin(string $url): void
    {
        // arrange
        /** @var Registration $registration */
        $registration = $this->em->getRepository(Registration::class)->findOneBy(['activity' => null]);
        $id = $registration->getId();

        $url = str_replace('id', strval($id), $url);

        // act
        $this->logout();
        $this->login([]);
        $this->client->request('GET', $url);

        // assert
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return iterable<array{string}>
     */
    public function noAccessProvider()
    {
        return [
            [$this->controller.'/edit/id'],
            [$this->controller.'/delete/id'],
        ];
    }
}
