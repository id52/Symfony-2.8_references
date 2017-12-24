<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160315141807 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sms_uslugi_ru_logs (id INT AUTO_INCREMENT NOT NULL, text VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, s_id VARCHAR(255) DEFAULT NULL, s_answer LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE envelopes (id INT AUTO_INCREMENT NOT NULL, workplace_id INT DEFAULT NULL, operator_id INT DEFAULT NULL, courier_id INT DEFAULT NULL, sgl_id INT DEFAULT NULL, sum INT NOT NULL, courier_datetime DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_58EDB319AC25FB46 (workplace_id), INDEX IDX_58EDB319584598A3 (operator_id), INDEX IDX_58EDB319E3D8151C (courier_id), INDEX IDX_58EDB319FF962108 (sgl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE services (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, _desc LONGTEXT NOT NULL, price INT NOT NULL, is_duplicate TINYINT(1) NOT NULL, is_discount TINYINT(1) NOT NULL, min_price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filials (id INT AUTO_INCREMENT NOT NULL, active TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, name_short VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, ips LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE legal_entities (id INT AUTO_INCREMENT NOT NULL, active TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, inn VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, person VARCHAR(255) NOT NULL, requisites VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cashboxes (id INT AUTO_INCREMENT NOT NULL, legal_entity_id INT NOT NULL, workplace_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, num VARCHAR(255) NOT NULL, inv_num VARCHAR(255) NOT NULL, INDEX IDX_AD520B9F6DEC420C (legal_entity_id), INDEX IDX_AD520B9FAC25FB46 (workplace_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cashiers_getting_logs (id INT AUTO_INCREMENT NOT NULL, supervisor_id INT DEFAULT NULL, cashier_id INT DEFAULT NULL, sum INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3AC7C32A19E9AC5F (supervisor_id), INDEX IDX_3AC7C32A2EDB0489 (cashier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, service_log_id INT DEFAULT NULL, file VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_E01FBE6A8C9F3610 (file), INDEX IDX_E01FBE6AD4A058B0 (service_log_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filials_ban_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, filial_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_22BC684BA76ED395 (user_id), INDEX IDX_22BC684B299B2577 (filial_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE action_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, action_type VARCHAR(255) NOT NULL, params LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, INDEX IDX_866E7D52A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE workplaces (id INT AUTO_INCREMENT NOT NULL, filial_id INT NOT NULL, legal_entity_id INT NOT NULL, active TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, sum INT NOT NULL, INDEX IDX_5C6CFBE6299B2577 (filial_id), INDEX IDX_5C6CFBE66DEC420C (legal_entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supervisors_getting_logs (id INT AUTO_INCREMENT NOT NULL, courier_id INT DEFAULT NULL, supervisor_id INT DEFAULT NULL, cgl_id INT DEFAULT NULL, sum INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E86D19C7E3D8151C (courier_id), INDEX IDX_E86D19C719E9AC5F (supervisor_id), INDEX IDX_E86D19C7FC402393 (cgl_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sessions (sess_id VARBINARY(128) NOT NULL, user_id INT DEFAULT NULL, sess_data BLOB NOT NULL, sess_time INT UNSIGNED NOT NULL, sess_lifetime INT NOT NULL, ip VARCHAR(255) DEFAULT NULL, INDEX IDX_9A609D13A76ED395 (user_id), PRIMARY KEY(sess_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE services_logs (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, workplace_id INT NOT NULL, operator_id INT NOT NULL, cashbox_id INT DEFAULT NULL, envelope_id INT DEFAULT NULL, params LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', sum INT DEFAULT 0 NOT NULL, date_giving DATE NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_74B69EEDED5CA9E6 (service_id), INDEX IDX_74B69EEDAC25FB46 (workplace_id), INDEX IDX_74B69EED584598A3 (operator_id), INDEX IDX_74B69EED61110C8F (cashbox_id), INDEX IDX_74B69EED4706CB17 (envelope_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, workplace_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, username VARCHAR(25) NOT NULL, password VARCHAR(64) NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, patronymic VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ips LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', auth_failure_info LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', force_change_pass TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), INDEX IDX_1483A5E9AC25FB46 (workplace_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_filials (user_id INT NOT NULL, filial_id INT NOT NULL, INDEX IDX_F94C0B06A76ED395 (user_id), INDEX IDX_F94C0B06299B2577 (filial_id), PRIMARY KEY(user_id, filial_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings (_key VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(_key)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE envelopes ADD CONSTRAINT FK_58EDB319AC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE envelopes ADD CONSTRAINT FK_58EDB319584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE envelopes ADD CONSTRAINT FK_58EDB319E3D8151C FOREIGN KEY (courier_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE envelopes ADD CONSTRAINT FK_58EDB319FF962108 FOREIGN KEY (sgl_id) REFERENCES supervisors_getting_logs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cashboxes ADD CONSTRAINT FK_AD520B9F6DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cashboxes ADD CONSTRAINT FK_AD520B9FAC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cashiers_getting_logs ADD CONSTRAINT FK_3AC7C32A19E9AC5F FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cashiers_getting_logs ADD CONSTRAINT FK_3AC7C32A2EDB0489 FOREIGN KEY (cashier_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AD4A058B0 FOREIGN KEY (service_log_id) REFERENCES services_logs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE filials_ban_logs ADD CONSTRAINT FK_22BC684BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filials_ban_logs ADD CONSTRAINT FK_22BC684B299B2577 FOREIGN KEY (filial_id) REFERENCES filials (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action_logs ADD CONSTRAINT FK_866E7D52A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workplaces ADD CONSTRAINT FK_5C6CFBE6299B2577 FOREIGN KEY (filial_id) REFERENCES filials (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workplaces ADD CONSTRAINT FK_5C6CFBE66DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supervisors_getting_logs ADD CONSTRAINT FK_E86D19C7E3D8151C FOREIGN KEY (courier_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE supervisors_getting_logs ADD CONSTRAINT FK_E86D19C719E9AC5F FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE supervisors_getting_logs ADD CONSTRAINT FK_E86D19C7FC402393 FOREIGN KEY (cgl_id) REFERENCES cashiers_getting_logs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE sessions ADD CONSTRAINT FK_9A609D13A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EEDED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EEDAC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EED584598A3 FOREIGN KEY (operator_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EED61110C8F FOREIGN KEY (cashbox_id) REFERENCES cashboxes (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE services_logs ADD CONSTRAINT FK_74B69EED4706CB17 FOREIGN KEY (envelope_id) REFERENCES envelopes (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9AC25FB46 FOREIGN KEY (workplace_id) REFERENCES workplaces (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users_filials ADD CONSTRAINT FK_F94C0B06A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_filials ADD CONSTRAINT FK_F94C0B06299B2577 FOREIGN KEY (filial_id) REFERENCES filials (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EED4706CB17');
        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EEDED5CA9E6');
        $this->addSql('ALTER TABLE filials_ban_logs DROP FOREIGN KEY FK_22BC684B299B2577');
        $this->addSql('ALTER TABLE workplaces DROP FOREIGN KEY FK_5C6CFBE6299B2577');
        $this->addSql('ALTER TABLE users_filials DROP FOREIGN KEY FK_F94C0B06299B2577');
        $this->addSql('ALTER TABLE cashboxes DROP FOREIGN KEY FK_AD520B9F6DEC420C');
        $this->addSql('ALTER TABLE workplaces DROP FOREIGN KEY FK_5C6CFBE66DEC420C');
        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EED61110C8F');
        $this->addSql('ALTER TABLE supervisors_getting_logs DROP FOREIGN KEY FK_E86D19C7FC402393');
        $this->addSql('ALTER TABLE envelopes DROP FOREIGN KEY FK_58EDB319AC25FB46');
        $this->addSql('ALTER TABLE cashboxes DROP FOREIGN KEY FK_AD520B9FAC25FB46');
        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EEDAC25FB46');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9AC25FB46');
        $this->addSql('ALTER TABLE envelopes DROP FOREIGN KEY FK_58EDB319FF962108');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AD4A058B0');
        $this->addSql('ALTER TABLE envelopes DROP FOREIGN KEY FK_58EDB319584598A3');
        $this->addSql('ALTER TABLE envelopes DROP FOREIGN KEY FK_58EDB319E3D8151C');
        $this->addSql('ALTER TABLE cashiers_getting_logs DROP FOREIGN KEY FK_3AC7C32A19E9AC5F');
        $this->addSql('ALTER TABLE cashiers_getting_logs DROP FOREIGN KEY FK_3AC7C32A2EDB0489');
        $this->addSql('ALTER TABLE filials_ban_logs DROP FOREIGN KEY FK_22BC684BA76ED395');
        $this->addSql('ALTER TABLE action_logs DROP FOREIGN KEY FK_866E7D52A76ED395');
        $this->addSql('ALTER TABLE supervisors_getting_logs DROP FOREIGN KEY FK_E86D19C7E3D8151C');
        $this->addSql('ALTER TABLE supervisors_getting_logs DROP FOREIGN KEY FK_E86D19C719E9AC5F');
        $this->addSql('ALTER TABLE sessions DROP FOREIGN KEY FK_9A609D13A76ED395');
        $this->addSql('ALTER TABLE services_logs DROP FOREIGN KEY FK_74B69EED584598A3');
        $this->addSql('ALTER TABLE users_filials DROP FOREIGN KEY FK_F94C0B06A76ED395');
        $this->addSql('DROP TABLE sms_uslugi_ru_logs');
        $this->addSql('DROP TABLE envelopes');
        $this->addSql('DROP TABLE services');
        $this->addSql('DROP TABLE filials');
        $this->addSql('DROP TABLE legal_entities');
        $this->addSql('DROP TABLE cashboxes');
        $this->addSql('DROP TABLE cashiers_getting_logs');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE filials_ban_logs');
        $this->addSql('DROP TABLE action_logs');
        $this->addSql('DROP TABLE workplaces');
        $this->addSql('DROP TABLE supervisors_getting_logs');
        $this->addSql('DROP TABLE sessions');
        $this->addSql('DROP TABLE services_logs');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE users_filials');
        $this->addSql('DROP TABLE settings');
    }
}
