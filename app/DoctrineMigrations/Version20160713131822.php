<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160713131822 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE consumable_tag_categories (id INT AUTO_INCREMENT NOT NULL, active TINYINT(1) NOT NULL, required TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumable_tag_category_consumable_tag (consumable_tag_category_id INT NOT NULL, consumable_tag_id INT NOT NULL, INDEX IDX_B8148FC1E8F40290 (consumable_tag_category_id), INDEX IDX_B8148FC1CEC764C5 (consumable_tag_id), PRIMARY KEY(consumable_tag_category_id, consumable_tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumables (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, filial_id INT DEFAULT NULL, orderman_id INT DEFAULT NULL, date DATETIME NOT NULL, appointment LONGTEXT NOT NULL, description LONGTEXT NOT NULL, sum INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9B2FDD308D9F6D38 (order_id), INDEX IDX_9B2FDD30299B2577 (filial_id), INDEX IDX_9B2FDD30EAA5E7A9 (orderman_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumable_tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consumable_tag_consumable (consumable_tag_id INT NOT NULL, consumable_id INT NOT NULL, INDEX IDX_F9D7E18BCEC764C5 (consumable_tag_id), INDEX IDX_F9D7E18BA94ADB61 (consumable_id), PRIMARY KEY(consumable_tag_id, consumable_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supervisor_repayments (id INT AUTO_INCREMENT NOT NULL, supervisor_id INT DEFAULT NULL, order_id INT DEFAULT NULL, sum INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FD5112A719E9AC5F (supervisor_id), INDEX IDX_FD5112A78D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, acquittanceman_id INT DEFAULT NULL, operator_id INT DEFAULT NULL, treasurer_id INT DEFAULT NULL, workplace_id INT DEFAULT NULL, appointment LONGTEXT NOT NULL, pin INT NOT NULL, sum INT NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E52FFDEE6330ECAC (acquittanceman_id), INDEX IDX_E52FFDEE584598A3 (operator_id), INDEX IDX_E52FFDEE55808438 (treasurer_id), INDEX IDX_E52FFDEEAC25FB46 (workplace_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consumable_tag_category_consumable_tag ADD CONSTRAINT FK_B8148FC1E8F40290 FOREIGN KEY (consumable_tag_category_id) REFERENCES consumable_tag_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumable_tag_category_consumable_tag ADD CONSTRAINT FK_B8148FC1CEC764C5 FOREIGN KEY (consumable_tag_id) REFERENCES consumable_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumables ADD CONSTRAINT FK_9B2FDD308D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE consumables ADD CONSTRAINT FK_9B2FDD30299B2577 FOREIGN KEY (filial_id) REFERENCES filials (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE consumables ADD CONSTRAINT FK_9B2FDD30EAA5E7A9 FOREIGN KEY (orderman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE consumable_tag_consumable ADD CONSTRAINT FK_F9D7E18BCEC764C5 FOREIGN KEY (consumable_tag_id) REFERENCES consumable_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consumable_tag_consumable ADD CONSTRAINT FK_F9D7E18BA94ADB61 FOREIGN KEY (consumable_id) REFERENCES consumables (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supervisor_repayments ADD CONSTRAINT FK_FD5112A719E9AC5F FOREIGN KEY (supervisor_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE supervisor_repayments ADD CONSTRAINT FK_FD5112A78D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE6330ECAC FOREIGN KEY (acquittanceman_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE55808438 FOREIGN KEY (treasurer_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEAC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE discounts CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE services_logs CHANGE num_blank num_blank VARCHAR(255) NOT NULL, CHANGE eeg_conclusion eeg_conclusion VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consumable_tag_category_consumable_tag DROP FOREIGN KEY FK_B8148FC1E8F40290');
        $this->addSql('ALTER TABLE consumable_tag_consumable DROP FOREIGN KEY FK_F9D7E18BA94ADB61');
        $this->addSql('ALTER TABLE consumable_tag_category_consumable_tag DROP FOREIGN KEY FK_B8148FC1CEC764C5');
        $this->addSql('ALTER TABLE consumable_tag_consumable DROP FOREIGN KEY FK_F9D7E18BCEC764C5');
        $this->addSql('ALTER TABLE consumables DROP FOREIGN KEY FK_9B2FDD308D9F6D38');
        $this->addSql('ALTER TABLE supervisor_repayments DROP FOREIGN KEY FK_FD5112A78D9F6D38');
        $this->addSql('DROP TABLE consumable_tag_categories');
        $this->addSql('DROP TABLE consumable_tag_category_consumable_tag');
        $this->addSql('DROP TABLE consumables');
        $this->addSql('DROP TABLE consumable_tags');
        $this->addSql('DROP TABLE consumable_tag_consumable');
        $this->addSql('DROP TABLE supervisor_repayments');
        $this->addSql('DROP TABLE orders');
        $this->addSql('ALTER TABLE discounts CHANGE position position INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE services_logs CHANGE eeg_conclusion eeg_conclusion VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE num_blank num_blank DATE NOT NULL');
    }
}
