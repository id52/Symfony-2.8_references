<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170410101010 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE referenceman_archive_boxes ADD reference_type_id INT UNSIGNED DEFAULT NULL, ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE referenceman_archive_boxes ADD CONSTRAINT FK_A15AFC00C23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE referenceman_archive_boxes ADD CONSTRAINT FK_A15AFC006DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_A15AFC00C23C293B ON referenceman_archive_boxes (reference_type_id)');
        $this->addSql('CREATE INDEX IDX_A15AFC006DEC420C ON referenceman_archive_boxes (legal_entity_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE referenceman_archive_boxes DROP FOREIGN KEY FK_A15AFC00C23C293B');
        $this->addSql('ALTER TABLE referenceman_archive_boxes DROP FOREIGN KEY FK_A15AFC006DEC420C');
        $this->addSql('DROP INDEX IDX_A15AFC00C23C293B ON referenceman_archive_boxes');
        $this->addSql('DROP INDEX IDX_A15AFC006DEC420C ON referenceman_archive_boxes');
        $this->addSql('ALTER TABLE referenceman_archive_boxes DROP reference_type_id, DROP legal_entity_id');
    }
}
