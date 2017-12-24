<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160930122525 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services ADD is_not_duplicate_price TINYINT(1) DEFAULT \'0\' NOT NULL, ADD is_not_revisit_price TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE duplicate_price duplicate_price INT DEFAULT 0, CHANGE revisit_price revisit_price INT DEFAULT 0');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services DROP is_not_duplicate_price, DROP is_not_revisit_price, CHANGE revisit_price revisit_price INT DEFAULT 0 NOT NULL, CHANGE duplicate_price duplicate_price INT DEFAULT 0 NOT NULL');
    }
}
