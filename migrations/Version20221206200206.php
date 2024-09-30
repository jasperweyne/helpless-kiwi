<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221206200206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api tokens';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_api_token (token VARCHAR(255) NOT NULL, account_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', client_id VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_35C7E1F39B6B5FBA (account_id), INDEX IDX_35C7E1F319EB6921 (client_id), PRIMARY KEY(token)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_trusted_client (id VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kiwi_api_token ADD CONSTRAINT FK_35C7E1F39B6B5FBA FOREIGN KEY (account_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('ALTER TABLE kiwi_api_token ADD CONSTRAINT FK_35C7E1F319EB6921 FOREIGN KEY (client_id) REFERENCES kiwi_trusted_client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_api_token DROP FOREIGN KEY FK_35C7E1F319EB6921');
        $this->addSql('DROP TABLE kiwi_api_token');
        $this->addSql('DROP TABLE kiwi_trusted_client');
    }
}
