<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240525202639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kiwi_registration CHANGE transferable transferable DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_waitlist ON kiwi_waitlist (person_id, option_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_waitlist ON kiwi_waitlist');
        $this->addSql('ALTER TABLE kiwi_registration CHANGE transferable transferable TINYINT(1) NOT NULL');
    }
}
