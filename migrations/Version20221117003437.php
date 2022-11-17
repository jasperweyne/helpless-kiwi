<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117003437 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_activity (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', location CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', primairy_author CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', target CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, color VARCHAR(255) NOT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, deadline DATETIME NOT NULL, image_updated_at DATETIME NOT NULL, capacity INT DEFAULT NULL, present INT DEFAULT NULL, visible_after DATETIME DEFAULT \'1970-01-01 00:00:00\', image_name VARCHAR(255) DEFAULT NULL, image_original_name VARCHAR(255) DEFAULT NULL, image_mime_type VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, image_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', UNIQUE INDEX UNIQ_CDE345B85E9E89CB (location), INDEX IDX_CDE345B8ED07F46C (primairy_author), INDEX IDX_CDE345B8466F2FFC (target), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_local_account (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', email VARCHAR(180) NOT NULL, given_name VARCHAR(180) NOT NULL, family_name VARCHAR(180) NOT NULL, password VARCHAR(255) DEFAULT NULL, oidc VARCHAR(255) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password_request_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_E78FD480E7927C74 (email), UNIQUE INDEX UNIQ_E78FD480D02D56A7 (oidc), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_location (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', address VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_log (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', discr VARCHAR(100) NOT NULL, time DATETIME NOT NULL, object_id VARCHAR(255) DEFAULT NULL, object_type VARCHAR(255) DEFAULT NULL, meta LONGTEXT NOT NULL, INDEX IDX_9798ECD4217BBB47 (person_id), INDEX search_idx (object_id, object_type), INDEX order_idx (time), INDEX discr_idx (discr), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_mail (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, sender VARCHAR(255) NOT NULL, sent_at DATETIME NOT NULL, INDEX IDX_3B8E2B3E217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_price_option (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', activity CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', target CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(100) NOT NULL, price INT NOT NULL, details LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', confirmation_msg VARCHAR(255) NOT NULL, INDEX IDX_165C18E4AC74095A (activity), INDEX IDX_165C18E4466F2FFC (target), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_recipient (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', mail CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_2661EF51217BBB47 (person_id), INDEX IDX_2661EF515126AC48 (mail), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_registration (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', option_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', activity CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', reserve_position VARCHAR(255) DEFAULT NULL, newdate DATETIME NOT NULL, deletedate DATETIME DEFAULT NULL, present TINYINT(1) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_63EB17A3A7C41D6F (option_id), INDEX IDX_63EB17A3217BBB47 (person_id), INDEX IDX_63EB17A3AC74095A (activity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_relation (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', group_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', person_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', description VARCHAR(255) DEFAULT NULL, INDEX IDX_31E0BABFE54D947 (group_id), INDEX IDX_31E0BAB217BBB47 (person_id), INDEX IDX_31E0BAB727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kiwi_taxonomy (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', parent CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, readonly TINYINT(1) NOT NULL, relationable TINYINT(1) DEFAULT NULL, subgroupable TINYINT(1) DEFAULT NULL, active TINYINT(1) NOT NULL, register TINYINT(1) DEFAULT NULL, INDEX IDX_9C85F4DF3D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B85E9E89CB FOREIGN KEY (location) REFERENCES kiwi_location (id)');
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B8ED07F46C FOREIGN KEY (primairy_author) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B8466F2FFC FOREIGN KEY (target) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_log ADD CONSTRAINT FK_9798ECD4217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('ALTER TABLE kiwi_mail ADD CONSTRAINT FK_3B8E2B3E217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('ALTER TABLE kiwi_price_option ADD CONSTRAINT FK_165C18E4AC74095A FOREIGN KEY (activity) REFERENCES kiwi_activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_price_option ADD CONSTRAINT FK_165C18E4466F2FFC FOREIGN KEY (target) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_recipient ADD CONSTRAINT FK_2661EF51217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('ALTER TABLE kiwi_recipient ADD CONSTRAINT FK_2661EF515126AC48 FOREIGN KEY (mail) REFERENCES kiwi_mail (id)');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3A7C41D6F FOREIGN KEY (option_id) REFERENCES kiwi_price_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3AC74095A FOREIGN KEY (activity) REFERENCES kiwi_activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BABFE54D947 FOREIGN KEY (group_id) REFERENCES kiwi_taxonomy (id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB217BBB47 FOREIGN KEY (person_id) REFERENCES kiwi_local_account (id)');
        $this->addSql('ALTER TABLE kiwi_relation ADD CONSTRAINT FK_31E0BAB727ACA70 FOREIGN KEY (parent_id) REFERENCES kiwi_relation (id)');
        $this->addSql('ALTER TABLE kiwi_taxonomy ADD CONSTRAINT FK_9C85F4DF3D8E604F FOREIGN KEY (parent) REFERENCES kiwi_taxonomy (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_price_option DROP FOREIGN KEY FK_165C18E4AC74095A');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3AC74095A');
        $this->addSql('ALTER TABLE kiwi_log DROP FOREIGN KEY FK_9798ECD4217BBB47');
        $this->addSql('ALTER TABLE kiwi_mail DROP FOREIGN KEY FK_3B8E2B3E217BBB47');
        $this->addSql('ALTER TABLE kiwi_recipient DROP FOREIGN KEY FK_2661EF51217BBB47');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3217BBB47');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB217BBB47');
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B85E9E89CB');
        $this->addSql('ALTER TABLE kiwi_recipient DROP FOREIGN KEY FK_2661EF515126AC48');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3A7C41D6F');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BAB727ACA70');
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B8ED07F46C');
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B8466F2FFC');
        $this->addSql('ALTER TABLE kiwi_price_option DROP FOREIGN KEY FK_165C18E4466F2FFC');
        $this->addSql('ALTER TABLE kiwi_relation DROP FOREIGN KEY FK_31E0BABFE54D947');
        $this->addSql('ALTER TABLE kiwi_taxonomy DROP FOREIGN KEY FK_9C85F4DF3D8E604F');
        $this->addSql('DROP TABLE kiwi_activity');
        $this->addSql('DROP TABLE kiwi_local_account');
        $this->addSql('DROP TABLE kiwi_location');
        $this->addSql('DROP TABLE kiwi_log');
        $this->addSql('DROP TABLE kiwi_mail');
        $this->addSql('DROP TABLE kiwi_price_option');
        $this->addSql('DROP TABLE kiwi_recipient');
        $this->addSql('DROP TABLE kiwi_registration');
        $this->addSql('DROP TABLE kiwi_relation');
        $this->addSql('DROP TABLE kiwi_taxonomy');
    }
}
