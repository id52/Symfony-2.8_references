<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161011124930 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blank_operator_referenceman_envelopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, operator_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, first_num INT UNSIGNED NOT NULL, amount INT NOT NULL, referenceman_applied DATETIME DEFAULT NULL, INDEX IDX_7EA7E20B584598A3 (operator_id), INDEX IDX_7EA7E20BBF9AF344 (referenceman_id), INDEX IDX_7EA7E20BC23C293B (reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes ADD CONSTRAINT FK_7EA7E20B584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes ADD CONSTRAINT FK_7EA7E20BBF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes ADD CONSTRAINT FK_7EA7E20BC23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE blank ADD operator_referenceman_envelope_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC46572C7BE4 FOREIGN KEY (operator_referenceman_envelope_id) REFERENCES blank_operator_referenceman_envelopes (id)');
        $this->addSql('CREATE INDEX IDX_3C2BC46572C7BE4 ON blank (operator_referenceman_envelope_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC46572C7BE4');
        $this->addSql('DROP TABLE blank_operator_referenceman_envelopes');
        $this->addSql('DROP INDEX IDX_3C2BC46572C7BE4 ON blank');
        $this->addSql('ALTER TABLE blank DROP operator_referenceman_envelope_id');
    }
}
