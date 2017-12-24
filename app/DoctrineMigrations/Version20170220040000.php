<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170220040000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `settings` (`_key`, `value`, `type`) VALUES (\'creating_orderman_archive_box_text\', \'Создание новой архивной коробки для Ордериста\', \'string\')');
        $this->addSql('INSERT INTO `settings` (`_key`, `value`, `type`) VALUES (\'creating_referenceman_archive_box_text\', \'Создание новой архивной коробки для Справковеда\', \'string\')');
        $this->addSql('INSERT INTO `settings` (`_key`, `value`, `type`) VALUES (\'current_orderman_archive_box_text\', \'Действующая архивная коробка для Ордериста. {{ number_box }} - номер действующей коробки\', \'string\')');
        $this->addSql('INSERT INTO `settings` (`_key`, `value`, `type`) VALUES (\'current_referenceman_archive_box_text\', \'Действующая архивная коробка для Справковеда. {{ number_box }} - номер действующей коробки\', \'string\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `settings` WHERE `_key`="creating_orderman_archive_box_text"');
        $this->addSql('DELETE FROM `settings` WHERE `_key`="creating_referenceman_archive_box_text"');
        $this->addSql('DELETE FROM `settings` WHERE `_key`="current_orderman_archive_box_text"');
        $this->addSql('DELETE FROM `settings` WHERE `_key`="current_referenceman_archive_box_text"');
    }
}
