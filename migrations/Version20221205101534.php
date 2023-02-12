<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221205101534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }


    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Create table and copy old data
        $this->addSql('CREATE TABLE kiwi_photo (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', image_updated_at DATETIME NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_original_name VARCHAR(255) DEFAULT NULL, image_mime_type VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, image_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO kiwi_photo (id, image_updated_at,image_name,image_original_name,image_mime_type,image_size,image_dimensions) SELECT UUID(), image_updated_at, image_name, image_original_name, image_mime_type, image_size, image_dimensions FROM kiwi_activity');

        // Link old data to new table
        $this->addSql('ALTER TABLE kiwi_activity ADD photo_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('UPDATE kiwi_activity, kiwi_photo SET kiwi_activity.photo_id = kiwi_photo.id WHERE kiwi_activity.image_name = kiwi_photo.image_name');

        // Cleanup old data
        $this->addSql('ALTER TABLE kiwi_activity DROP image_updated_at, DROP image_name, DROP image_original_name, DROP image_mime_type, DROP image_size, DROP image_dimensions');

        // Create indices
        $this->addSql('ALTER TABLE kiwi_activity ADD CONSTRAINT FK_CDE345B87E9E4C8C FOREIGN KEY (photo_id) REFERENCES kiwi_photo (id)');
        $this->addSql('CREATE INDEX IDX_CDE345B87E9E4C8C ON kiwi_activity (photo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Check for duplicates
        if (count($this->connection->fetchAllAssociative('SELECT photo_id, COUNT(photo_id) FROM kiwi_activity GROUP BY photo_id HAVING COUNT(photo_id) > 1')) > 0) {
            $this->throwIrreversibleMigrationException('Can\'t safely reduce from 1-to-n relationship from photo to activity into 1-to-1 relationship');
        }

        // Create columns and copy table data
        $this->addSql('ALTER TABLE kiwi_activity ADD image_updated_at DATETIME DEFAULT NULL, ADD image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD image_original_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD image_mime_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD image_size INT DEFAULT NULL, ADD image_dimensions LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('UPDATE kiwi_activity, kiwi_photo SET kiwi_activity.image_updated_at = kiwi_photo.image_updated_at, kiwi_activity.image_name = kiwi_photo.image_name, kiwi_activity.image_original_name = kiwi_photo.image_original_name, kiwi_activity.image_mime_type = kiwi_photo.image_mime_type, kiwi_activity.image_size = kiwi_photo.image_size, kiwi_activity.image_dimensions = kiwi_photo.image_dimensions WHERE kiwi_activity.photo_id = kiwi_photo.id');

        // Cleanup table data
        $this->addSql('ALTER TABLE kiwi_activity DROP FOREIGN KEY FK_CDE345B87E9E4C8C');
        $this->addSql('DROP INDEX IDX_CDE345B87E9E4C8C ON kiwi_activity');
        $this->addSql('ALTER TABLE kiwi_activity DROP photo_id, CHANGE image_updated_at image_updated_at DATETIME NOT NULL');
        $this->addSql('DROP TABLE kiwi_photo');
    }
}
