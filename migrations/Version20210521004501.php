<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210521004501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove OAuth tokens from the database and add foreign key relations to local account.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE kiwi_oauth2_access_token');
        $this->addSql('ALTER TABLE kiwi_registration CHANGE person_id person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('CREATE INDEX IDX_63EB17A3217BBB47 ON kiwi_registration (person_id)');
        $this->addSql('ALTER TABLE kiwi_recipient CHANGE person_id person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE kiwi_recipient ADD CONSTRAINT FK_2661EF51217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('CREATE INDEX IDX_2661EF51217BBB47 ON kiwi_recipient (person_id)');
        $this->addSql('ALTER TABLE kiwi_mail ADD CONSTRAINT FK_3B8E2B3E217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('CREATE INDEX IDX_3B8E2B3E217BBB47 ON kiwi_mail (person_id)');
        $this->addSql('ALTER TABLE kiwi_log ADD CONSTRAINT FK_9798ECD4217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('CREATE INDEX IDX_9798ECD4217BBB47 ON kiwi_log (person_id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('CREATE INDEX IDX_31E0BAB217BBB47 ON kiwi_relation (person_id)');
        $this->addSql('ALTER TABLE kiwi_local_account ADD family_name VARCHAR(180) NOT NULL, CHANGE name given_name VARCHAR(180) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_oauth2_access_token (id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, access_token LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE kiwi_local_account ADD name VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP given_name, DROP family_name');
        $this->addSql('ALTER TABLE kiwi_log DROP FOREIGN KEY FK_9798ECD4217BBB47');
        $this->addSql('DROP INDEX IDX_9798ECD4217BBB47 ON kiwi_log');
        $this->addSql('ALTER TABLE kiwi_mail DROP FOREIGN KEY FK_3B8E2B3E217BBB47');
        $this->addSql('DROP INDEX IDX_3B8E2B3E217BBB47 ON kiwi_mail');
        $this->addSql('ALTER TABLE kiwi_recipient DROP FOREIGN KEY FK_2661EF51217BBB47');
        $this->addSql('DROP INDEX IDX_2661EF51217BBB47 ON kiwi_recipient');
        $this->addSql('ALTER TABLE kiwi_recipient CHANGE person_id person_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3217BBB47');
        $this->addSql('DROP INDEX IDX_63EB17A3217BBB47 ON kiwi_registration');
        $this->addSql('ALTER TABLE kiwi_registration CHANGE person_id person_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB217BBB47');
        $this->addSql('DROP INDEX IDX_31E0BAB217BBB47 ON kiwi_relation');
    }
}
