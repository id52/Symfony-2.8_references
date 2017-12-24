<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170316023724 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank ADD leading_zeros INT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes ADD leading_zeros INT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE blank_stockman_envelopes ADD leading_zeros INT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD leading_zeros INT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes ADD leading_zeros INT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes ADD leading_zeros INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP leading_zeros');
        $this->addSql('ALTER TABLE blank_operator_envelopes DROP leading_zeros');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes DROP leading_zeros');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes DROP leading_zeros');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes DROP leading_zeros');
        $this->addSql('ALTER TABLE blank_stockman_envelopes DROP leading_zeros');
    }
}
