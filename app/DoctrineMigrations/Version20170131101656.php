<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170131101656 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX user__index ON blank (stockman_id, referenceman_id, operator_id, status)');
        $this->addSql('CREATE INDEX stockman__index ON blank (stockman_id, status, legal_entity_id, reference_type_id, serie, number)');
        $this->addSql('CREATE INDEX referenceman__index ON blank (referenceman_id, status, legal_entity_id, reference_type_id, serie, number, referenceman_applied)');
        $this->addSql('CREATE INDEX operator__index ON blank (operator_id, status, legal_entity_id, reference_type_id, serie, number, operator_applied)');
        $this->addSql('CREATE INDEX stockman_referenceman_envelope__index ON blank (stockman_id, referenceman_id, referenceman_envelope_id, legal_entity_id, reference_type_id, status, number, referenceman_applied)');
        $this->addSql('CREATE INDEX referenceman_operator_envelope__index ON blank (referenceman_id, operator_id, operator_envelope_id, status, operator_applied)');
        $this->addSql('CREATE INDEX operator_referenceman_envelope__index ON blank (operator_id, referenceman_id, operator_referenceman_envelope_id, status, referenceman_applied)');
        $this->addSql('CREATE INDEX referenceman_stockman_envelope__index ON blank (referenceman_id, stockman_id, stockman_envelope_id, status, stockman_applied)');
        $this->addSql('CREATE INDEX referenceman_referenceman_envelope__index ON blank (referenceman_id, old_referenceman_id, referenceman_referenceman_envelope_id, status, referenceman_applied)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX user__index ON blank');
        $this->addSql('DROP INDEX stockman__index ON blank');
        $this->addSql('DROP INDEX referenceman__index ON blank');
        $this->addSql('DROP INDEX operator__index ON blank');
        $this->addSql('DROP INDEX stockman_referenceman_envelope__index ON blank');
        $this->addSql('DROP INDEX referenceman_operator_envelope__index ON blank');
        $this->addSql('DROP INDEX operator_referenceman_envelope__index ON blank');
        $this->addSql('DROP INDEX referenceman_stockman_envelope__index ON blank');
        $this->addSql('DROP INDEX referenceman_referenceman_envelope__index ON blank');
    }
}
