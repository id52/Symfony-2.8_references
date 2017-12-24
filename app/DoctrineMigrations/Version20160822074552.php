<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160822074552 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP INDEX IDX_3C2BC465D4A058B0, ADD UNIQUE INDEX UNIQ_3C2BC465D4A058B0 (service_log_id)');
        $this->addSql('ALTER TABLE services_logs ADD blanks_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EED28F740F FOREIGN KEY (blanks_id) REFERENCES blank (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_74B69EED28F740F ON services_logs (blanks_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP INDEX UNIQ_3C2BC465D4A058B0, ADD INDEX IDX_3C2BC465D4A058B0 (service_log_id)');
        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EED28F740F');
        $this->addSql('DROP INDEX UNIQ_74B69EED28F740F ON services_logs');
        $this->addSql('ALTER TABLE services_logs DROP blanks_id');
    }
}
