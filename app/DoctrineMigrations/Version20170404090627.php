<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170404090627 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank ADD replaced_by_blank_with_stamp_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465563BCDB FOREIGN KEY (replaced_by_blank_with_stamp_id) REFERENCES blank (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3C2BC465563BCDB ON blank (replaced_by_blank_with_stamp_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC465563BCDB');
        $this->addSql('DROP INDEX UNIQ_3C2BC465563BCDB ON blank');
        $this->addSql('ALTER TABLE blank DROP replaced_by_blank_with_stamp_id');
    }
}
