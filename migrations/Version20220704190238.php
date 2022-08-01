<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220704190238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Archive groups from the "standard arrangement"';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM kiwi_taxonomy');
        if ($count > 0) {
            $id = $this->connection->fetchOne('SELECT ' . $this->platform->getGuidExpression());
            $this->addSql(
                'INSERT INTO kiwi_taxonomy (id, title, `description`, `readonly`, relationable, subgroupable, active, register) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [$id, 'Archive', 'An archive of historic groups', 1, 0, 1, 0, 0]
            );
            $this->addSql('UPDATE kiwi_taxonomy t SET t.parent = ? WHERE t.parent IS NULL AND t.id <> ?', [$id, $id]);
            $this->addSql('UPDATE kiwi_taxonomy t SET t.active = 0, t.register = 0');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
