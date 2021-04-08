<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * @codeCoverageIgnore
 */
final class Version20210204213609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_oauth2_access_token (id VARCHAR(255) NOT NULL, access_token JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE kiwi_oauth2access_token');
        $this->addSql('ALTER TABLE kiwi_activity ADD present INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B85E9E89CB FOREIGN KEY (location) REFERENCES kiwi_location (id)');
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B8ED07F46C FOREIGN KEY (primairy_author) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B8466F2FFC FOREIGN KEY (target) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_price_option ADD CONSTRAINT FK_165C18E4AC74095A FOREIGN KEY (activity) REFERENCES kiwi_activity (id)');
        $this->addSql('ALTER TABLE kiwi_price_option ADD CONSTRAINT FK_165C18E4466F2FFC FOREIGN KEY (target) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_registration ADD present TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3A7C41D6F FOREIGN KEY (option_id) REFERENCES kiwi_price_option (id)');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3AC74095A FOREIGN KEY (activity) REFERENCES kiwi_activity (id)');
        $this->addSql('ALTER TABLE kiwi_taxonomy ADD CONSTRAINT FK_9C85F4DF3D8E604F FOREIGN KEY (parent) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BABFE54D947 FOREIGN KEY (group_id) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB727ACA70 FOREIGN KEY (parent_id) REFERENCES kiwi_relation (id)');
        $this->addSql('ALTER TABLE kiwi_recipient ADD CONSTRAINT FK_2661EF515126AC48 FOREIGN KEY (mail) REFERENCES kiwi_mail (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_oauth2access_token (id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, access_token JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE kiwi_oauth2_access_token');
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B85E9E89CB');
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B8ED07F46C');
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B8466F2FFC');
        $this->addSql('ALTER TABLE kiwi_activity DROP present');
        $this->addSql('ALTER TABLE kiwi_price_option DROP FOREIGN KEY FK_165C18E4AC74095A');
        $this->addSql('ALTER TABLE kiwi_price_option DROP FOREIGN KEY FK_165C18E4466F2FFC');
        $this->addSql('ALTER TABLE kiwi_recipient DROP FOREIGN KEY FK_2661EF515126AC48');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3A7C41D6F');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3AC74095A');
        $this->addSql('ALTER TABLE kiwi_registration DROP present');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BABFE54D947');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB727ACA70');
        $this->addSql('ALTER TABLE kiwi_taxonomy DROP FOREIGN KEY FK_9C85F4DF3D8E604F');
    }
}
