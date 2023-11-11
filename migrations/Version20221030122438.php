<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221030122438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change activity.location from unique index to normal index';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_activity DROP INDEX UNIQ_CDE345B85E9E89CB, ADD INDEX IDX_CDE345B85E9E89CB (location)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_activity DROP INDEX IDX_CDE345B85E9E89CB, ADD UNIQUE INDEX UNIQ_CDE345B85E9E89CB (location)');
    }
}
