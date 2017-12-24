<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161031122957 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE consumable_doc_types (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT \'1\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consumables DROP FOREIGN KEY FK_9B2FDD30299B2577');
        $this->addSql('DROP INDEX IDX_9B2FDD30299B2577 ON consumables');
        $this->addSql('ALTER TABLE consumables ADD consumable_doc_type_id INT UNSIGNED DEFAULT NULL, DROP filial_id');
        $this->addSql('ALTER TABLE consumables ADD CONSTRAINT FK_9B2FDD30AAB23804 FOREIGN KEY (consumable_doc_type_id) REFERENCES consumable_doc_types (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B2FDD30AAB23804 ON consumables (consumable_doc_type_id)');
        $this->addSql('ALTER TABLE images ADD crop_coords LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumables DROP FOREIGN KEY FK_9B2FDD30AAB23804');
        $this->addSql('DROP TABLE consumable_doc_types');
        $this->addSql('DROP INDEX IDX_9B2FDD30AAB23804 ON consumables');
        $this->addSql('ALTER TABLE consumables ADD filial_id INT DEFAULT NULL, DROP consumable_doc_type_id');
        $this->addSql('ALTER TABLE consumables ADD CONSTRAINT FK_9B2FDD30299B2577 FOREIGN KEY (filial_id) REFERENCES filials (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B2FDD30299B2577 ON consumables (filial_id)');
        $this->addSql('ALTER TABLE images DROP crop_coords');
    }
}
