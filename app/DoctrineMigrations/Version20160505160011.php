<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160505160011 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('SET @i=0');
        $this->addSql('UPDATE services SET position=(@i:=@i+1)');
        $this->addSql('UPDATE services SET active=1');
    }

    public function down(Schema $schema)
    {
    }
}
