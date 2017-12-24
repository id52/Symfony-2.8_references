<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161224001119 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blank_life_logs (id INT AUTO_INCREMENT NOT NULL, start_user_id INT DEFAULT NULL, end_user_id INT DEFAULT NULL, blank_id INT UNSIGNED DEFAULT NULL, workplace_id INT DEFAULT NULL, operation_status VARCHAR(255) DEFAULT NULL, envelope_id INT DEFAULT NULL, envelope_type VARCHAR(255) DEFAULT NULL, start_status VARCHAR(255) DEFAULT NULL, end_status VARCHAR(255) DEFAULT NULL, correct_blank_number INT DEFAULT NULL, service_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_1C8BF6A957AB8F46 (start_user_id), INDEX IDX_1C8BF6A932A1827C (end_user_id), INDEX IDX_1C8BF6A92B7727CD (blank_id), INDEX IDX_1C8BF6A9AC25FB46 (workplace_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blank_life_logs ADD CONSTRAINT FK_1C8BF6A957AB8F46 FOREIGN KEY (start_user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_life_logs ADD CONSTRAINT FK_1C8BF6A932A1827C FOREIGN KEY (end_user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_life_logs ADD CONSTRAINT FK_1C8BF6A92B7727CD FOREIGN KEY (blank_id) REFERENCES blank (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_life_logs ADD CONSTRAINT FK_1C8BF6A9AC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE blank_life_logs');
    }
}
