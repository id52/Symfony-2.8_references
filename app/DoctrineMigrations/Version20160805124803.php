<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160805124803 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blank (id INT UNSIGNED AUTO_INCREMENT NOT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, stockman_id INT DEFAULT NULL, operator_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, service_log_id INT DEFAULT NULL, operator_envelope_id INT UNSIGNED DEFAULT NULL, referenceman_envelope_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, number INT UNSIGNED NOT NULL, status VARCHAR(255) NOT NULL, referenceman_applied DATETIME DEFAULT NULL, operator_applied DATETIME DEFAULT NULL, service_log_applied DATETIME DEFAULT NULL, INDEX IDX_3C2BC465C23C293B (reference_type_id), INDEX IDX_3C2BC465F52B8FA5 (stockman_id), INDEX IDX_3C2BC465584598A3 (operator_id), INDEX IDX_3C2BC465BF9AF344 (referenceman_id), INDEX IDX_3C2BC465D4A058B0 (service_log_id), INDEX IDX_3C2BC465EEBFA3CB (operator_envelope_id), INDEX IDX_3C2BC4656BA3AB92 (referenceman_envelope_id), UNIQUE INDEX uniqueConstraint (serie, number, reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blank_operator_envelopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, operator_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, first_num INT UNSIGNED NOT NULL, amount INT NOT NULL, operator_applied DATETIME DEFAULT NULL, INDEX IDX_B9A8EDBA584598A3 (operator_id), INDEX IDX_B9A8EDBABF9AF344 (referenceman_id), INDEX IDX_B9A8EDBAC23C293B (reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blank_referenceman_envelopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, stockman_id INT DEFAULT NULL, referenceman_id INT DEFAULT NULL, reference_type_id INT UNSIGNED DEFAULT NULL, created_at DATETIME NOT NULL, serie VARCHAR(255) NOT NULL, first_num INT UNSIGNED NOT NULL, amount INT NOT NULL, referenceman_applied DATETIME DEFAULT NULL, INDEX IDX_7717ACE8F52B8FA5 (stockman_id), INDEX IDX_7717ACE8BF9AF344 (referenceman_id), INDEX IDX_7717ACE8C23C293B (reference_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reference_types (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465C23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465F52B8FA5 FOREIGN KEY (stockman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465BF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465D4A058B0 FOREIGN KEY (service_log_id) REFERENCES services_logs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465EEBFA3CB FOREIGN KEY (operator_envelope_id) REFERENCES blank_operator_envelopes (id)');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4656BA3AB92 FOREIGN KEY (referenceman_envelope_id) REFERENCES blank_referenceman_envelopes (id)');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD CONSTRAINT FK_B9A8EDBA584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD CONSTRAINT FK_B9A8EDBABF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD CONSTRAINT FK_B9A8EDBAC23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes ADD CONSTRAINT FK_7717ACE8F52B8FA5 FOREIGN KEY (stockman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes ADD CONSTRAINT FK_7717ACE8BF9AF344 FOREIGN KEY (referenceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes ADD CONSTRAINT FK_7717ACE8C23C293B FOREIGN KEY (reference_type_id) REFERENCES reference_types (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC465EEBFA3CB');
        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4656BA3AB92');
        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC465C23C293B');
        $this->addSql('ALTER TABLE blank_operator_envelopes DROP FOREIGN KEY FK_B9A8EDBAC23C293B');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes DROP FOREIGN KEY FK_7717ACE8C23C293B');
        $this->addSql('DROP TABLE blank');
        $this->addSql('DROP TABLE blank_operator_envelopes');
        $this->addSql('DROP TABLE blank_referenceman_envelopes');
        $this->addSql('DROP TABLE reference_types');
    }
}
