<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200404110821 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE violation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE violation_act (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, violation_id INT DEFAULT NULL, violation_date DATETIME NOT NULL, INDEX IDX_C92671F8A76ED395 (user_id), INDEX IDX_C92671F87386118A (violation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE violation_act ADD CONSTRAINT FK_C92671F8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE violation_act ADD CONSTRAINT FK_C92671F87386118A FOREIGN KEY (violation_id) REFERENCES violation (id)');
        $this->addSql('ALTER TABLE user DROP violation');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE violation_act DROP FOREIGN KEY FK_C92671F87386118A');
        $this->addSql('DROP TABLE violation');
        $this->addSql('DROP TABLE violation_act');
        $this->addSql('ALTER TABLE user ADD violation SMALLINT DEFAULT NULL');
    }
}
