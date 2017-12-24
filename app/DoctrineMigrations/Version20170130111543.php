<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170130111543 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE orderman_consumable_boxes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, orderman_id INT DEFAULT NULL, acquittanceman_id INT DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, INDEX IDX_6E38C461EAA5E7A9 (orderman_id), INDEX IDX_6E38C4616330ECAC (acquittanceman_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orderman_consumable_boxes ADD CONSTRAINT FK_6E38C461EAA5E7A9 FOREIGN KEY (orderman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orderman_consumable_boxes ADD CONSTRAINT FK_6E38C4616330ECAC FOREIGN KEY (acquittanceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE consumables ADD orderman_consumable_box_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE consumables ADD CONSTRAINT FK_9B2FDD303BD7B95B FOREIGN KEY (orderman_consumable_box_id) REFERENCES orderman_consumable_boxes (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B2FDD303BD7B95B ON consumables (orderman_consumable_box_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumables DROP FOREIGN KEY FK_9B2FDD303BD7B95B');
        $this->addSql('DROP TABLE orderman_consumable_boxes');
        $this->addSql('DROP INDEX IDX_9B2FDD303BD7B95B ON consumables');
        $this->addSql('ALTER TABLE consumables DROP orderman_consumable_box_id');
    }
}
