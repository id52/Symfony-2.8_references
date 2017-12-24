<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170529045308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE agreements (service_id INT NOT NULL, workplace_id INT NOT NULL, guarantor_id INT DEFAULT NULL, executor_id INT NOT NULL, type VARCHAR(255) DEFAULT \'bilateral\' NOT NULL, INDEX IDX_27234E805C3575A7 (guarantor_id), INDEX IDX_27234E808ABD09BB (executor_id), INDEX IDX_27234E80ED5CA9E6 (service_id), INDEX IDX_27234E80AC25FB46 (workplace_id), PRIMARY KEY(service_id, workplace_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agreements ADD CONSTRAINT FK_27234E805C3575A7 FOREIGN KEY (guarantor_id) REFERENCES legal_entities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agreements ADD CONSTRAINT FK_27234E808ABD09BB FOREIGN KEY (executor_id) REFERENCES legal_entities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agreements ADD CONSTRAINT FK_27234E80ED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE agreements ADD CONSTRAINT FK_27234E80AC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE agreements');
    }
}
