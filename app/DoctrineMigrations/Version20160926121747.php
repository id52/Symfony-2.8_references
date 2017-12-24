<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160926121747 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services_logs ADD medical_center_error_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EED31D6E86A FOREIGN KEY (medical_center_error_id) REFERENCES services_logs (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_74B69EED31D6E86A ON services_logs (medical_center_error_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EED31D6E86A');
        $this->addSql('DROP INDEX IDX_74B69EED31D6E86A ON services_logs');
        $this->addSql('ALTER TABLE services_logs DROP medical_center_error_id');
    }
}
