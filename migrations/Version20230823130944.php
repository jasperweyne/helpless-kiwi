<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823130944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_waitlist (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', person_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', option_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C9C473CA217BBB47 (person_id), INDEX IDX_C9C473CAA7C41D6F (option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kiwi_waitlist ADD CONSTRAINT FK_C9C473CA217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_waitlist ADD CONSTRAINT FK_C9C473CAA7C41D6F FOREIGN KEY (option_id) REFERENCES kiwi_price_option (id)');
        $this->addSql('ALTER TABLE kiwi_registration DROP reserve_position');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE kiwi_waitlist');
        $this->addSql('ALTER TABLE kiwi_registration ADD reserve_position VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
