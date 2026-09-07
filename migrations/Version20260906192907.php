<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260906192907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agent_assignment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, polling_station VARCHAR(255) DEFAULT NULL, assigned_by VARCHAR(150) NOT NULL, assigned_at DATETIME NOT NULL, status VARCHAR(16) NOT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, agent_id INTEGER NOT NULL, election_id INTEGER NOT NULL, district_id INTEGER DEFAULT NULL, CONSTRAINT FK_418395B53414710B FOREIGN KEY (agent_id) REFERENCES party_agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_418395B5A708DAFF FOREIGN KEY (election_id) REFERENCES election (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_418395B5B08FA272 FOREIGN KEY (district_id) REFERENCES district (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_418395B53414710B ON agent_assignment (agent_id)');
        $this->addSql('CREATE INDEX IDX_418395B5A708DAFF ON agent_assignment (election_id)');
        $this->addSql('CREATE INDEX IDX_418395B5B08FA272 ON agent_assignment (district_id)');
        $this->addSql('CREATE TABLE observation_photo (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, caption VARCHAR(255) DEFAULT NULL, position INTEGER NOT NULL, uploaded_at DATETIME NOT NULL, created_at DATETIME NOT NULL, report_id INTEGER NOT NULL, CONSTRAINT FK_AFDE96A54BD2A4C0 FOREIGN KEY (report_id) REFERENCES observation_report (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AFDE96A54BD2A4C0 ON observation_photo (report_id)');
        $this->addSql('CREATE TABLE observation_report (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, submitted_at DATETIME NOT NULL, description CLOB NOT NULL, voting_start_observed BOOLEAN NOT NULL, voting_end_observed BOOLEAN NOT NULL, irregularities BOOLEAN NOT NULL, irregularity_details CLOB DEFAULT NULL, estimated_turnout INTEGER DEFAULT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, assignment_id INTEGER NOT NULL, CONSTRAINT FK_7144D1A7D19302F8 FOREIGN KEY (assignment_id) REFERENCES agent_assignment (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7144D1A7D19302F8 ON observation_report (assignment_id)');
        $this->addSql('CREATE TABLE observation_result (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, observed_votes INTEGER NOT NULL, report_id INTEGER NOT NULL, party_id INTEGER NOT NULL, CONSTRAINT FK_A60167304BD2A4C0 FOREIGN KEY (report_id) REFERENCES observation_report (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A6016730213C1059 FOREIGN KEY (party_id) REFERENCES political_party (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A60167304BD2A4C0 ON observation_result (report_id)');
        $this->addSql('CREATE INDEX IDX_A6016730213C1059 ON observation_result (party_id)');
        $this->addSql('CREATE TABLE party_agent (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(30) DEFAULT NULL, agent_code VARCHAR(50) NOT NULL, credentials CLOB DEFAULT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, enabled BOOLEAN NOT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, party_id INTEGER NOT NULL, CONSTRAINT FK_394EBC32213C1059 FOREIGN KEY (party_id) REFERENCES political_party (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_394EBC32E7927C74 ON party_agent (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_394EBC32DA8E8A7B ON party_agent (agent_code)');
        $this->addSql('CREATE INDEX IDX_394EBC32213C1059 ON party_agent (party_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE agent_assignment');
        $this->addSql('DROP TABLE observation_photo');
        $this->addSql('DROP TABLE observation_report');
        $this->addSql('DROP TABLE observation_result');
        $this->addSql('DROP TABLE party_agent');
    }
}
