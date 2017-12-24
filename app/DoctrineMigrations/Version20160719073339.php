<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160719073339 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE consumable_tag_category_consumable_tag');
        $this->addSql('ALTER TABLE users ADD orderman_sum INT NOT NULL, ADD supervisor_sum INT NOT NULL');
        $this->addSql('ALTER TABLE consumables ADD name LONGTEXT NOT NULL, ADD archive_number LONGTEXT NOT NULL, ADD doc_type LONGTEXT NOT NULL, ADD doc_date DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE consumable_tags ADD tag_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE consumable_tags ADD CONSTRAINT FK_6B367A40E8FE702 FOREIGN KEY (tag_category_id) REFERENCES consumable_tag_categories (id)');
        $this->addSql('CREATE INDEX IDX_6B367A40E8FE702 ON consumable_tags (tag_category_id)');
        $this->addSql('ALTER TABLE orders ADD residual INT NOT NULL, DROP status');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE consumable_tag_category_consumable_tag (consumable_tag_category_id INT NOT NULL, consumable_tag_id INT NOT NULL, INDEX IDX_B8148FC1E8F40290 (consumable_tag_category_id), INDEX IDX_B8148FC1CEC764C5 (consumable_tag_id), PRIMARY KEY(consumable_tag_category_id, consumable_tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consumable_tag_category_consumable_tag ADD CONSTRAINT FK_B8148FC1CEC764C5 FOREIGN KEY (consumable_tag_id) REFERENCES consumable_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumable_tag_category_consumable_tag ADD CONSTRAINT FK_B8148FC1E8F40290 FOREIGN KEY (consumable_tag_category_id) REFERENCES consumable_tag_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumable_tags DROP FOREIGN KEY FK_6B367A40E8FE702');
        $this->addSql('DROP INDEX IDX_6B367A40E8FE702 ON consumable_tags');
        $this->addSql('ALTER TABLE consumable_tags DROP tag_category_id');
        $this->addSql('ALTER TABLE consumables DROP name, DROP archive_number, DROP doc_type, DROP doc_date, DROP updated_at');
        $this->addSql('ALTER TABLE orders ADD status TINYINT(1) NOT NULL, DROP residual');
        $this->addSql('ALTER TABLE users DROP orderman_sum, DROP supervisor_sum');
    }
}
