<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202230206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM kiwi_relation WHERE kiwi_relation.person_id IS NULL');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_62894749727ACA70');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_62894749FE54D947');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB217BBB47');
        $this->addSql('DROP INDEX IDX_31E0BAB727ACA70 ON kiwi_relation');
        $this->addSql('ALTER TABLE kiwi_relation DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE kiwi_relation DROP id, DROP parent_id, DROP description, CHANGE person_id person_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BABFE54D947 FOREIGN KEY (group_id) REFERENCES kiwi_taxonomy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_relation ADD PRIMARY KEY (person_id, group_id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BABFE54D947');
        $this->addSql('ALTER TABLE kiwi_relation DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE kiwi_relation ADD id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD parent_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE person_id person_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('CREATE INDEX IDX_31E0BAB727ACA70 ON kiwi_relation (parent_id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_62894749727ACA70 FOREIGN KEY (parent_id) REFERENCES kiwi_relation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_62894749FE54D947 FOREIGN KEY (group_id) REFERENCES kiwi_taxonomy (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
    }
}
