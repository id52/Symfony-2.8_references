<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170130092532 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE referenceman_archive_boxes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, operator_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, number LONGTEXT DEFAULT NULL, serie VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_A15AFC00584598A3 (operator_id), INDEX IDX_A15AFC00BF9AF344 (referenceman_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE referenceman_archive_boxes ADD CONSTRAINT FK_A15AFC00584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE referenceman_archive_boxes ADD CONSTRAINT FK_A15AFC00BF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank ADD referenceman_archive_box_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4651AD8AAD FOREIGN KEY (referenceman_archive_box_id) REFERENCES referenceman_archive_boxes (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3C2BC4651AD8AAD ON blank (referenceman_archive_box_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4651AD8AAD');
        $this->addSql('DROP TABLE referenceman_archive_boxes');
        $this->addSql('DROP INDEX IDX_3C2BC4651AD8AAD ON blank');
        $this->addSql('ALTER TABLE blank DROP referenceman_archive_box_id');
    }
}
