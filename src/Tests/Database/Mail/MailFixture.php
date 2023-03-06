<?php

namespace App\Tests\Database\Mail;

use App\Entity\Mail\Mail;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MailFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $mail = new Mail();
        $mail->setTitle('Kiwi - mail title');
        $content = json_encode([
            'html' => '<h1>Content</h1>',
            'plain' => 'Content',
        ]);
        $mail->setContent($content);
        $mail->setSender('SenderPerson');
        $mail->setSentAt(new \DateTime());

        $manager->persist($mail);
        $manager->flush();
    }
}
