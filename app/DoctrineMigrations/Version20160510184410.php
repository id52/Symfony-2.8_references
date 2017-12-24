<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160510184410 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE legal_entities ADD short_name VARCHAR(255) NOT NULL');
        $this->addSql('SET @i=0');
        $this->addSql('UPDATE legal_entities SET short_name=(@i:=@i+1)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FA2113243EE4B093 ON legal_entities (short_name)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_FA2113243EE4B093 ON legal_entities');
        $this->addSql('ALTER TABLE legal_entities DROP short_name');
    }
}
