<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210204213609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add activity presency counts';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE kiwi_oauth2access_token TO kiwi_oauth2_access_token');
        $this->addSql('ALTER TABLE kiwi_activity ADD present INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kiwi_registration ADD present TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE kiwi_oauth2_access_token TO kiwi_oauth2access_token');
        $this->addSql('ALTER TABLE kiwi_activity DROP present');
        $this->addSql('ALTER TABLE kiwi_registration DROP present');
    }
}
