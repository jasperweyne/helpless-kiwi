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
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_price_option ADD CONSTRAINT FK_165C18E4AC74095A FOREIGN KEY (activity) REFERENCES kiwi_activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3A7C41D6F');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3A7C41D6F FOREIGN KEY (option_id) REFERENCES kiwi_price_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3AC74095A');
        $this->addSql('ALTER TABLE kiwi_registration ADD CONSTRAINT FK_63EB17A3AC74095A FOREIGN KEY (activity) REFERENCES kiwi_activity (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kiwi_price_option DROP FOREIGN KEY FK_165C18E4AC74095A');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3A7C41D6F');
        $this->addSql('ALTER TABLE kiwi_registration DROP FOREIGN KEY FK_63EB17A3AC74095A');
    }
}
