<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170321095651 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM blank WHERE id IN (SELECT * FROM (SELECT id FROM (SELECT DISTINCT id, legal_entity_id, CONCAT_WS("-", serie, number, reference_type_id) AS k FROM blank) AS tmp GROUP BY k HAVING COUNT(*) > 1) AS tmp2)');
        $this->addSql('DROP INDEX uniqueConstraint ON blank');
        $this->addSql('CREATE UNIQUE INDEX uniqueConstraint ON blank (serie, number, reference_type_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX uniqueConstraint ON blank (serie, number, reference_type_id, legal_entity_id)');
    }
}
