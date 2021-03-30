<?php

declare(strict_types=1);

namespace Kiwi\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201005164015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kiwi_activity (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', location CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', primairy_author CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', target CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', title VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, description LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, color VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, start DATETIME NOT NULL, end DATETIME NOT NULL, deadline DATETIME NOT NULL, image_updated_at DATETIME NOT NULL, capacity INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, image_original_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, image_mime_type VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, image_size INT DEFAULT NULL, image_dimensions LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:simple_array)\', INDEX IDX_CDE345B8ED07F46C (primairy_author), UNIQUE INDEX UNIQ_CDE345B85E9E89CB (location), INDEX IDX_CDE345B8466F2FFC (target), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_local_account (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', email VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci, name VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci, password VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, roles JSON NOT NULL, password_request_token VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, password_requested_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_E78FD480E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_location (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', address VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_log (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', discr VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, time DATETIME NOT NULL, object_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, object_type VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, person_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', meta LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, INDEX order_idx (time), INDEX search_idx (object_id, object_type), INDEX discr_idx (discr), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_mail (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', person_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', title VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, content LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, sender VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, sent_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_oauth2access_token (id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, access_token JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_price_option (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', activity CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', target CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', title VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, price INT NOT NULL, details JSON NOT NULL, confirmation_msg VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_165C18E4AC74095A (activity), INDEX IDX_165C18E4466F2FFC (target), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_recipient (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', mail CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', person_id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', INDEX IDX_2661EF515126AC48 (mail), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_registration (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', option_id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', activity CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', person_id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', reserve_position VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, newdate DATETIME NOT NULL, deletedate DATETIME DEFAULT NULL, INDEX IDX_63EB17A3AC74095A (activity), INDEX IDX_63EB17A3A7C41D6F (option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_relation (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', group_id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', description VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, person_id CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', INDEX IDX_31E0BABFE54D947 (group_id), INDEX IDX_31E0BAB727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('CREATE TABLE kiwi_taxonomy (id CHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', parent CHAR(36) DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:guid)\', title VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, description LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, readonly TINYINT(1) NOT NULL, relationable TINYINT(1) DEFAULT NULL, subgroupable TINYINT(1) DEFAULT NULL, active TINYINT(1) NOT NULL, register TINYINT(1) DEFAULT NULL, INDEX IDX_9C85F4DF3D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE kiwi_activity');

        $this->addSql('DROP TABLE kiwi_local_account');

        $this->addSql('DROP TABLE kiwi_location');

        $this->addSql('DROP TABLE kiwi_log');

        $this->addSql('DROP TABLE kiwi_mail');

        $this->addSql('DROP TABLE kiwi_oauth2access_token');

        $this->addSql('DROP TABLE kiwi_price_option');

        $this->addSql('DROP TABLE kiwi_recipient');

        $this->addSql('DROP TABLE kiwi_registration');

        $this->addSql('DROP TABLE kiwi_relation');

        $this->addSql('DROP TABLE kiwi_taxonomy');
    }
}
