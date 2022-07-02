<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220702202424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_note (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', author_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', location_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT NOT NULL, INDEX IDX_A5157D62F675F31B (author_id), INDEX IDX_A5157D6264D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kiwi_note ADD CONSTRAINT FK_A5157D62F675F31B FOREIGN KEY (author_id) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_note ADD CONSTRAINT FK_A5157D6264D218E FOREIGN KEY (location_id) REFERENCES kiwi_location (id)');
        $this->addSql('ALTER TABLE kiwi_activity DROP INDEX UNIQ_CDE345B85E9E89CB, ADD INDEX IDX_CDE345B85E9E89CB (location)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE kiwi_note');
        $this->addSql('ALTER TABLE kiwi_activity DROP INDEX IDX_CDE345B85E9E89CB, ADD UNIQUE INDEX UNIQ_CDE345B85E9E89CB (location)');
    }
}
