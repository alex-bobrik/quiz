<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200419085039 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quiz ADD checked_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA922199DB86 FOREIGN KEY (checked_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A412FA922199DB86 ON quiz (checked_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA922199DB86');
        $this->addSql('DROP INDEX IDX_A412FA922199DB86 ON quiz');
        $this->addSql('ALTER TABLE quiz DROP checked_by_id');
    }
}
