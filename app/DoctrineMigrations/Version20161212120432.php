<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161212120432 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX uniqueConstraint ON blank');
        $this->addSql('ALTER TABLE blank ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank ADD CONSTRAINT FK_3C2BC4656DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_3C2BC4656DEC420C ON blank (legal_entity_id)');
        $this->addSql('CREATE UNIQUE INDEX uniqueConstraint ON blank (serie, number, reference_type_id, legal_entity_id)');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes ADD CONSTRAINT FK_7EA7E20B6DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_7EA7E20B6DEC420C ON blank_operator_referenceman_envelopes (legal_entity_id)');
        $this->addSql('ALTER TABLE blank_stockman_envelopes ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_stockman_envelopes ADD CONSTRAINT FK_5DA8E2576DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_5DA8E2576DEC420C ON blank_stockman_envelopes (legal_entity_id)');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_operator_envelopes ADD CONSTRAINT FK_B9A8EDBA6DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_B9A8EDBA6DEC420C ON blank_operator_envelopes (legal_entity_id)');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes ADD CONSTRAINT FK_7717ACE86DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_7717ACE86DEC420C ON blank_referenceman_envelopes (legal_entity_id)');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes ADD CONSTRAINT FK_16B22FF96DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_16B22FF96DEC420C ON blank_referenceman_referenceman_envelopes (legal_entity_id)');
        $this->addSql('ALTER TABLE blank_logs ADD legal_entity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blank_logs ADD CONSTRAINT FK_42BCE7D56DEC420C FOREIGN KEY (legal_entity_id) REFERENCES legal_entities (id)');
        $this->addSql('CREATE INDEX IDX_42BCE7D56DEC420C ON blank_logs (legal_entity_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blank DROP FOREIGN KEY FK_3C2BC4656DEC420C');
        $this->addSql('DROP INDEX IDX_3C2BC4656DEC420C ON blank');
        $this->addSql('DROP INDEX uniqueConstraint ON blank');
        $this->addSql('ALTER TABLE blank DROP legal_entity_id');
        $this->addSql('CREATE UNIQUE INDEX uniqueConstraint ON blank (serie, number, reference_type_id)');
        $this->addSql('ALTER TABLE blank_logs DROP FOREIGN KEY FK_42BCE7D56DEC420C');
        $this->addSql('DROP INDEX IDX_42BCE7D56DEC420C ON blank_logs');
        $this->addSql('ALTER TABLE blank_logs DROP legal_entity_id');
        $this->addSql('ALTER TABLE blank_operator_envelopes DROP FOREIGN KEY FK_B9A8EDBA6DEC420C');
        $this->addSql('DROP INDEX IDX_B9A8EDBA6DEC420C ON blank_operator_envelopes');
        $this->addSql('ALTER TABLE blank_operator_envelopes DROP legal_entity_id');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes DROP FOREIGN KEY FK_7EA7E20B6DEC420C');
        $this->addSql('DROP INDEX IDX_7EA7E20B6DEC420C ON blank_operator_referenceman_envelopes');
        $this->addSql('ALTER TABLE blank_operator_referenceman_envelopes DROP legal_entity_id');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes DROP FOREIGN KEY FK_7717ACE86DEC420C');
        $this->addSql('DROP INDEX IDX_7717ACE86DEC420C ON blank_referenceman_envelopes');
        $this->addSql('ALTER TABLE blank_referenceman_envelopes DROP legal_entity_id');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes DROP FOREIGN KEY FK_16B22FF96DEC420C');
        $this->addSql('DROP INDEX IDX_16B22FF96DEC420C ON blank_referenceman_referenceman_envelopes');
        $this->addSql('ALTER TABLE blank_referenceman_referenceman_envelopes DROP legal_entity_id');
        $this->addSql('ALTER TABLE blank_stockman_envelopes DROP FOREIGN KEY FK_5DA8E2576DEC420C');
        $this->addSql('DROP INDEX IDX_5DA8E2576DEC420C ON blank_stockman_envelopes');
        $this->addSql('ALTER TABLE blank_stockman_envelopes DROP legal_entity_id');
    }
}
