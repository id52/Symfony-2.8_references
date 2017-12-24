<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160701000000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `services_logs` ADD `first_name` varchar(255) NOT NULL');
        $this->addSql('ALTER TABLE `services_logs` ADD `last_name` varchar(255) NOT NULL');
        $this->addSql('ALTER TABLE `services_logs` ADD `patronymic` varchar(255) NOT NULL');
        $this->addSql('ALTER TABLE `services_logs` ADD `birthday` DATE NOT NULL');
        $this->addSql('ALTER TABLE `services_logs` ADD `num_blank` DATE NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `services_logs` DROP COLUMN `first_name`');
        $this->addSql('ALTER TABLE `services_logs` DROP COLUMN `last_name`');
        $this->addSql('ALTER TABLE `services_logs` DROP COLUMN `patronymic`');
        $this->addSql('ALTER TABLE `services_logs` DROP COLUMN `birthday`');
        $this->addSql('ALTER TABLE `services_logs` DROP COLUMN `num_blank`');
    }
}
