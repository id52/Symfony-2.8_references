<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161013061253 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blank_referenceman_referenceman_envelopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, old_referenceman_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, first_num INT UNSIGNED NOT NULL, amount INT NOT NULL, referenceman_applied DATETIME DEFAULT NULL, INDEX IDX_16B22FF9273E5BFB (old_referenceman_id), INDEX IDX_16B22FF9BF9AF344 (referenceman_id), INDEX IDX_16B22FF9C23C293B (reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes ADD CONSTRAINT FK_16B22FF9273E5BFB FOREIGN KEY (old_referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes ADD CONSTRAINT FK_16B22FF9BF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes ADD CONSTRAINT FK_16B22FF9C23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE blank ADD referenceman_referenceman_envelope_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4652BFFA4CA FOREIGN KEY (referenceman_referenceman_envelope_id) REFERENCES blank_referenceman_referenceman_envelopes (id)');
        $this->addSql('CREATE INDEX IDX_3C2BC4652BFFA4CA ON blank (referenceman_referenceman_envelope_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4652BFFA4CA');
        $this->addSql('DROP TABLE blank_referenceman_referenceman_envelopes');
        $this->addSql('DROP INDEX IDX_3C2BC4652BFFA4CA ON blank');
        $this->addSql('ALTER TABLE blank DROP referenceman_referenceman_envelope_id');
    }
}
