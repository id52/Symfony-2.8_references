<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170413093327 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE operator_schedules (id INT AUTO_INCREMENT NOT NULL, operator_id INT DEFAULT NULL, date DATE DEFAULT NULL, INDEX IDX_F7E32349584598A3 (operator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operator_schedules ADD CONSTRAINT FK_F7E32349584598A3 FOREIGN KEY (operator_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE blank_operator_envelopes DROP FOREIGN KEY FK_B9A8EDBA6DEC420C');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD CONSTRAINT FK_B9A8EDBA6DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE operator_schedules');
        $this->addSql('ALTER TABLE blank_operator_envelopes DROP FOREIGN KEY FK_B9A8EDBA6DEC420C');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD CONSTRAINT FK_B9A8EDBA6DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
    }
}
