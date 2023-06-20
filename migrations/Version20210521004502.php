<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210521004502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove rows with broken foreign keys';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE r FROM kiwi_registration r WHERE NOT EXISTS (SELECT * FROM kiwi_local_account a WHERE a.id = r.person_id) AND r.person_id IS NOT NULL');
        $this->addSql('DELETE r FROM kiwi_recipient r WHERE NOT EXISTS (SELECT * FROM kiwi_local_account a WHERE a.id = r.person_id)');
        $this->addSql('DELETE r FROM kiwi_mail r WHERE NOT EXISTS (SELECT * FROM kiwi_local_account a WHERE a.id = r.person_id) AND r.person_id IS NOT NULL');
        $this->addSql('DELETE r FROM kiwi_log r WHERE NOT EXISTS (SELECT * FROM kiwi_local_account a WHERE a.id = r.person_id) AND r.person_id IS NOT NULL');
        $this->addSql('DELETE r FROM kiwi_relation r WHERE NOT EXISTS (SELECT * FROM kiwi_local_account a WHERE a.id = r.person_id)');
    }

    public function down(Schema $schema): void
    {
        // not applicable
    }
}
