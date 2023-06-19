<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523231314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_api_token DROP FOREIGN KEY FK_35C7E1F39B6B5FBA');
        $this->addSql('ALTER TABLE kiwi_api_token ADD CONSTRAINT FK_35C7E1F39B6B5FBA FOREIGN KEY (account_id) REFERENCES kiwi_local_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB217BBB47');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_log DROP FOREIGN KEY FK_9798ECD4217BBB47');
        $this->addSql('ALTER TABLE kiwi_log ADD CONSTRAINT FK_9798ECD4217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE kiwi_mail DROP FOREIGN KEY FK_3B8E2B3E217BBB47');
        $this->addSql('ALTER TABLE kiwi_mail ADD CONSTRAINT FK_3B8E2B3E217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE kiwi_recipient DROP FOREIGN KEY FK_2661EF51217BBB47');
        $this->addSql('ALTER TABLE kiwi_recipient ADD CONSTRAINT FK_2661EF51217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3217BBB47');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_api_token DROP FOREIGN KEY FK_35C7E1F39B6B5FBA');
        $this->addSql('ALTER TABLE kiwi_api_token ADD CONSTRAINT FK_35C7E1F39B6B5FBA FOREIGN KEY (account_id) REFERENCES kiwi_local_account (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_log DROP FOREIGN KEY FK_9798ECD4217BBB47');
        $this->addSql('ALTER TABLE kiwi_log ADD CONSTRAINT FK_9798ECD4217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_mail DROP FOREIGN KEY FK_3B8E2B3E217BBB47');
        $this->addSql('ALTER TABLE kiwi_mail ADD CONSTRAINT FK_3B8E2B3E217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_recipient DROP FOREIGN KEY FK_2661EF51217BBB47');
        $this->addSql('ALTER TABLE kiwi_recipient ADD CONSTRAINT FK_2661EF51217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3217BBB47');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB217BBB47');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
