<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161102122411 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank CHANGE serie serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes CHANGE serie serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_stockman_envelopes CHANGE serie serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_operator_envelopes CHANGE serie serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes CHANGE serie serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes CHANGE serie serie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_logs CHANGE serie serie VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE blank_logs CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE blank_operator_envelopes CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE blank_stockman_envelopes CHANGE serie serie VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
