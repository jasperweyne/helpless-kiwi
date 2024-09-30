<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240101182057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kiwi_local_account CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE kiwi_price_option CHANGE details details JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kiwi_local_account CHANGE roles roles LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE kiwi_price_option CHANGE details details LONGTEXT NOT NULL');
    }
}
