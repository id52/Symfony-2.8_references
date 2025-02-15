<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160719124659 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumable_tags DROP FOREIGN KEY FK_6B367A40E8FE702');
        $this->addSql('ALTER TABLE consumable_tags ADD CONSTRAINT FK_6B367A40E8FE702 FOREIGN KEY (tag_category_id) REFERENCES consumable_tag_categories (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumable_tags DROP FOREIGN KEY FK_6B367A40E8FE702');
        $this->addSql('ALTER TABLE consumable_tags ADD CONSTRAINT FK_6B367A40E8FE702 FOREIGN KEY (tag_category_id) REFERENCES consumable_tag_categories (id)');
    }
}
