<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160804154209 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services DROP is_duplicate');
        $this->addSql('ALTER TABLE supervisors_getting_logs DROP FOREIGN KEY FK_E86D19C7FC402393');
        $this->addSql('DROP INDEX IDX_E86D19C7FC402393 ON supervisors_getting_logs');
        $this->addSql('ALTER TABLE supervisors_getting_logs DROP cgl_id');
        $this->addSql('ALTER TABLE envelopes ADD supervisor_id INT DEFAULT NULL, ADD supervisor_datetime DATETIME DEFAULT NULL, ADD supervisor_accepted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE envelopes ADD CONSTRAINT FK_58EDB31919E9AC5F FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_58EDB31919E9AC5F ON envelopes (supervisor_id)');
        $this->addSql('ALTER TABLE orders ADD updated_at DATETIME NOT NULL');
        $this->addSql('INSERT INTO `settings` (`_key`, `value`, `type`) VALUES ("orderman_close_order_sum", 1000, "integer")');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE envelopes DROP FOREIGN KEY FK_58EDB31919E9AC5F');
        $this->addSql('DROP INDEX IDX_58EDB31919E9AC5F ON envelopes');
        $this->addSql('ALTER TABLE envelopes DROP supervisor_id, DROP supervisor_datetime, DROP supervisor_accepted_at');
        $this->addSql('ALTER TABLE orders DROP updated_at');
        $this->addSql('ALTER TABLE services ADD is_duplicate TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE supervisors_getting_logs ADD cgl_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supervisors_getting_logs ADD CONSTRAINT FK_E86D19C7FC402393 FOREIGN KEY (cgl_id) REFERENCES cashiers_getting_logs (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E86D19C7FC402393 ON supervisors_getting_logs (cgl_id)');
        $this->addSql('DELETE FROM `settings` WHERE `_key`="orderman_close_order_sum"');
    }
}
