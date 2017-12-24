<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161226112138 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank ADD penalty_admin_id INT DEFAULT NULL, ADD penalty_sum INT UNSIGNED DEFAULT 0 NOT NULL, ADD penalty_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC465135AA722 FOREIGN KEY (penalty_admin_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3C2BC465135AA722 ON blank (penalty_admin_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC465135AA722');
        $this->addSql('DROP INDEX IDX_3C2BC465135AA722 ON blank');
        $this->addSql('ALTER TABLE blank DROP penalty_admin_id, DROP penalty_sum, DROP penalty_date');
    }
}
