<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160819090516 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blank_logs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, stockman_id INT DEFAULT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, first_num INT UNSIGNED NOT NULL, amount INT NOT NULL, INDEX IDX_42BCE7D5F52B8FA5 (stockman_id), INDEX IDX_42BCE7D5C23C293B (reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blank_logs ADD CONSTRAINT FK_42BCE7D5F52B8FA5 FOREIGN KEY (stockman_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE blank_logs ADD CONSTRAINT FK_42BCE7D5C23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE blank ADD blank_log_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4654C4FDCE9 FOREIGN KEY (blank_log_id) REFERENCES blank_logs (id)');
        $this->addSql('CREATE INDEX IDX_3C2BC4654C4FDCE9 ON blank (blank_log_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4654C4FDCE9');
        $this->addSql('DROP TABLE blank_logs');
        $this->addSql('DROP INDEX IDX_3C2BC4654C4FDCE9 ON blank');
        $this->addSql('ALTER TABLE blank DROP blank_log_id');
    }
}
