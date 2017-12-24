<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161222202837 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('REPLACE INTO `settings` (`_key`, `value`, `type`) VALUES
                ("operator_blanks_on_hands_lost_title", "[Текст] Предупреждение на странице подтверждения утери бланка: {Заголовок}", "string"),
                ("operator_blanks_on_hands_lost_text",  "[Текст] Предупреждение на странице подтверждения утери бланка: {Текст}",     "string")
            ;
        ');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DELETE FROM `settings` WHERE `_key` IN (
                "operator_blanks_on_hands_lost_title",
                "operator_blanks_on_hands_lost_text"
            );
        ');
    }
}
