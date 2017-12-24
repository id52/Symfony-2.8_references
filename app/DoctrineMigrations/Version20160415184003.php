<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160415184003 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE legal_entities ADD checking_account VARCHAR(255) NOT NULL, ADD bank_name VARCHAR(255) NOT NULL, ADD correspondent_account VARCHAR(255) NOT NULL, ADD bik VARCHAR(255) NOT NULL, ADD kpp VARCHAR(255) NOT NULL, ADD person_genitive VARCHAR(255) NOT NULL, ADD phone VARCHAR(255) NOT NULL, CHANGE requisites license VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE legal_entities ADD requisites VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP license, DROP checking_account, DROP bank_name, DROP correspondent_account, DROP bik, DROP kpp, DROP person_genitive, DROP phone');
    }
}
