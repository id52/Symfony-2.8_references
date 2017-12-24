<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160823114716 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4656BA3AB92');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4656BA3AB92 FOREIGN KEY (referenceman_envelope_id) REFERENCES blank_referenceman_envelopes (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4656BA3AB92');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4656BA3AB92 FOREIGN KEY (referenceman_envelope_id) REFERENCES blank_referenceman_envelopes (id)');
    }
}
