<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161110073542 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE brigades (id INT UNSIGNED AUTO_INCREMENT NOT NULL, legal_entity_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_9530009C6DEC420C (legal_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specialties (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, eeg TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE men (id INT UNSIGNED AUTO_INCREMENT NOT NULL, brigade_id INT UNSIGNED DEFAULT NULL, specialty_id INT UNSIGNED DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, patronymic VARCHAR(255) NOT NULL, first_name_genitive VARCHAR(255) NOT NULL, last_name_genitive VARCHAR(255) NOT NULL, patronymic_genitive VARCHAR(255) NOT NULL, INDEX IDX_DCE52DC539A88F2 (brigade_id), INDEX IDX_DCE52DC9A353316 (specialty_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE brigades ADD CONSTRAINT FK_9530009C6DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE men ADD CONSTRAINT FK_DCE52DC539A88F2 FOREIGN KEY (brigade_id) REFERENCES brigades (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE men ADD CONSTRAINT FK_DCE52DC9A353316 FOREIGN KEY (specialty_id) REFERENCES specialties (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE men DROP FOREIGN KEY FK_DCE52DC539A88F2');
        $this->addSql('ALTER TABLE men DROP FOREIGN KEY FK_DCE52DC9A353316');
        $this->addSql('DROP TABLE brigades');
        $this->addSql('DROP TABLE specialties');
        $this->addSql('DROP TABLE men');
    }
}
