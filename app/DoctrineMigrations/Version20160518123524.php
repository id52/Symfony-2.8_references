<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160518123524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE service_log_category (service_log_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_B7B3A7FBD4A058B0 (service_log_id), INDEX IDX_B7B3A7FB12469DE2 (category_id), PRIMARY KEY(service_log_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service_log_category ADD CONSTRAINT FK_B7B3A7FBD4A058B0 FOREIGN KEY (service_log_id) REFERENCES services_logs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_log_category ADD CONSTRAINT FK_B7B3A7FB12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services ADD driver_reference TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE users ADD power_attorney VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EED12469DE2');
        $this->addSql('DROP INDEX IDX_74B69EED12469DE2 ON services_logs');
        $this->addSql('ALTER TABLE services_logs DROP category_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE service_log_category');
        $this->addSql('ALTER TABLE services DROP driver_reference');
        $this->addSql('ALTER TABLE services_logs ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EED12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_74B69EED12469DE2 ON services_logs (category_id)');
        $this->addSql('ALTER TABLE users DROP power_attorney');
    }
}
