<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161007105308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blank_stockman_envelopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, stockman_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, first_num INT UNSIGNED NOT NULL, amount INT NOT NULL, stockman_applied DATETIME DEFAULT NULL, INDEX IDX_5DA8E257F52B8FA5 (stockman_id), INDEX IDX_5DA8E257BF9AF344 (referenceman_id), INDEX IDX_5DA8E257C23C293B (reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blank_stockman_envelopes ADD CONSTRAINT FK_5DA8E257F52B8FA5 FOREIGN KEY (stockman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_stockman_envelopes ADD CONSTRAINT FK_5DA8E257BF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_stockman_envelopes ADD CONSTRAINT FK_5DA8E257C23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE blank ADD old_referenceman_id INT DEFAULT NULL, ADD stockman_envelope_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465273E5BFB FOREIGN KEY (old_referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC46595D0D7FB FOREIGN KEY (stockman_envelope_id) REFERENCES blank_stockman_envelopes (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3C2BC465273E5BFB ON blank (old_referenceman_id)');
        $this->addSql('CREATE INDEX IDX_3C2BC46595D0D7FB ON blank (stockman_envelope_id)');
        $this->addSql('ALTER TABLE blank ADD stockman_applied DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC46595D0D7FB');
        $this->addSql('DROP TABLE blank_stockman_envelopes');
        $this->addSql('DROP INDEX IDX_3C2BC465273E5BFB ON blank');
        $this->addSql('DROP INDEX IDX_3C2BC46595D0D7FB ON blank');
        $this->addSql('ALTER TABLE blank DROP old_referenceman_id, DROP stockman_envelope_id');
        $this->addSql('ALTER TABLE blank DROP stockman_applied');
    }
}
