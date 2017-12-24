<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161101073840 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumables DROP doc_type');
        $this->addSql('ALTER TABLE orders ADD parent_id INT DEFAULT NULL, ADD status LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE727ACA70 FOREIGN KEY (parent_id) REFERENCES orders (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E52FFDEE727ACA70 ON orders (parent_id)');
        $this->addSql("UPDATE `orders` SET `status`='createdByTreasurer' WHERE `status`='' AND `operator_id` IS NULL AND `treasurer_id` IS NOT NULL AND `acquittanceman_id` IS NOT NULL AND `sum` = `residual`");
        $this->addSql("UPDATE `orders` SET `status`='issuedByOperator' WHERE `status`='' AND `operator_id` IS NOT NULL AND `treasurer_id` IS NOT NULL AND `acquittanceman_id` IS NOT NULL AND `workplace_id` IS NOT NULL");
        $this->addSql("UPDATE `orders` SET `status`='closedBySupervisor' WHERE `status`='' AND `operator_id` IS NOT NULL AND `treasurer_id` IS NOT NULL AND `acquittanceman_id` IS NOT NULL AND `workplace_id` IS NOT NULL AND `sum` > 0 AND `residual` = 0");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumables ADD doc_type LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE727ACA70');
        $this->addSql('DROP INDEX IDX_E52FFDEE727ACA70 ON orders');
        $this->addSql('ALTER TABLE orders DROP parent_id, DROP status');
    }
}
