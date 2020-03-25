<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200211094309 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quiz_question DROP FOREIGN KEY FK_6033B00BBCB134CE');
        $this->addSql('DROP INDEX IDX_6033B00BBCB134CE ON quiz_question');
        $this->addSql('ALTER TABLE quiz_question CHANGE questions_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_question ADD CONSTRAINT FK_6033B00B1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_6033B00B1E27F6BF ON quiz_question (question_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quiz_question DROP FOREIGN KEY FK_6033B00B1E27F6BF');
        $this->addSql('DROP INDEX IDX_6033B00B1E27F6BF ON quiz_question');
        $this->addSql('ALTER TABLE quiz_question CHANGE question_id questions_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_question ADD CONSTRAINT FK_6033B00BBCB134CE FOREIGN KEY (questions_id) REFERENCES question (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6033B00BBCB134CE ON quiz_question (questions_id)');
    }
}
