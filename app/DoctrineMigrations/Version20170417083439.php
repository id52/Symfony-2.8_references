<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170417083439 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE shift_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, filial_id INT DEFAULT NULL, date DATE NOT NULL, start_time DATETIME DEFAULT NULL, end_time DATETIME DEFAULT NULL, closed TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_C5B6B993A76ED395 (user_id), INDEX IDX_C5B6B993299B2577 (filial_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shift_logs ADD CONSTRAINT FK_C5B6B993A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE shift_logs ADD CONSTRAINT FK_C5B6B993299B2577 FOREIGN KEY (filial_id) REFERENCES filials (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE shift_logs');
    }
}
