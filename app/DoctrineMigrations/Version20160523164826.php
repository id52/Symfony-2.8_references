<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160523164826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discounts (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE services_discounts (service_id INT NOT NULL, discount_id INT NOT NULL, active TINYINT(1) NOT NULL, sum INT NOT NULL, INDEX IDX_AB1C8701ED5CA9E6 (service_id), INDEX IDX_AB1C87014C7C611F (discount_id), PRIMARY KEY(service_id, discount_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE services_discounts ADD CONSTRAINT FK_AB1C8701ED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services_discounts ADD CONSTRAINT FK_AB1C87014C7C611F FOREIGN KEY (discount_id) REFERENCES discounts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services DROP is_discount, DROP min_price');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services_discounts DROP FOREIGN KEY FK_AB1C87014C7C611F');
        $this->addSql('DROP TABLE discounts');
        $this->addSql('DROP TABLE services_discounts');
        $this->addSql('ALTER TABLE services ADD is_discount TINYINT(1) NOT NULL, ADD min_price INT NOT NULL');
    }
}
