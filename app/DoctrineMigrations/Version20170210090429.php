<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170210090429 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE operator_replacement_logs (id INT AUTO_INCREMENT NOT NULL, predecessor_id INT DEFAULT NULL, successor_id INT DEFAULT NULL, created_at DATETIME NOT NULL, removed_at DATETIME DEFAULT NULL, INDEX IDX_E4A5975F68C90015 (predecessor_id), INDEX IDX_E4A5975F7323E667 (successor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operator_replacement_logs ADD CONSTRAINT FK_E4A5975F68C90015 FOREIGN KEY (predecessor_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE operator_replacement_logs ADD CONSTRAINT FK_E4A5975F7323E667 FOREIGN KEY (successor_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users ADD successor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E97323E667 FOREIGN KEY (successor_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E97323E667 ON users (successor_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE operator_replacement_logs');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E97323E667');
        $this->addSql('DROP INDEX UNIQ_1483A5E97323E667 ON users');
        $this->addSql('ALTER TABLE users DROP successor_id');
    }
}
