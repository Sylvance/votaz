<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260906185115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(200) NOT NULL, bio CLOB DEFAULT NULL, motto VARCHAR(255) DEFAULT NULL, ballot_position INTEGER DEFAULT 0 NOT NULL, status VARCHAR(16) NOT NULL, declared_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, election_id INTEGER NOT NULL, party_id INTEGER DEFAULT NULL, voter_id INTEGER DEFAULT NULL, district_id INTEGER DEFAULT NULL, CONSTRAINT FK_C8B28E44A708DAFF FOREIGN KEY (election_id) REFERENCES election (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C8B28E44213C1059 FOREIGN KEY (party_id) REFERENCES political_party (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C8B28E44EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C8B28E44B08FA272 FOREIGN KEY (district_id) REFERENCES district (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C8B28E44A708DAFF ON candidate (election_id)');
        $this->addSql('CREATE INDEX IDX_C8B28E44213C1059 ON candidate (party_id)');
        $this->addSql('CREATE INDEX IDX_C8B28E44EBB4B8AD ON candidate (voter_id)');
        $this->addSql('CREATE INDEX IDX_C8B28E44B08FA272 ON candidate (district_id)');
        $this->addSql('CREATE TABLE district (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(20) NOT NULL, name VARCHAR(160) NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31C1548777153098 ON district (code)');
        $this->addSql('CREATE TABLE election (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(200) NOT NULL, code VARCHAR(100) NOT NULL, type VARCHAR(16) NOT NULL, description CLOB DEFAULT NULL, registration_start_at DATETIME DEFAULT NULL, registration_end_at DATETIME DEFAULT NULL, nomination_start_at DATETIME DEFAULT NULL, nomination_end_at DATETIME DEFAULT NULL, voting_start_at DATETIME NOT NULL, voting_end_at DATETIME NOT NULL, results_published_at DATETIME DEFAULT NULL, status VARCHAR(20) NOT NULL, reason CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, district_id INTEGER DEFAULT NULL, CONSTRAINT FK_DCA03800B08FA272 FOREIGN KEY (district_id) REFERENCES district (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DCA0380077153098 ON election (code)');
        $this->addSql('CREATE INDEX IDX_DCA03800B08FA272 ON election (district_id)');
        $this->addSql('CREATE TABLE election_registration (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, status VARCHAR(16) NOT NULL, receipt_number VARCHAR(20) DEFAULT NULL, rejection_reason CLOB DEFAULT NULL, registered_at DATETIME DEFAULT NULL, verified_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, election_id INTEGER NOT NULL, voter_id INTEGER NOT NULL, CONSTRAINT FK_9E86507BA708DAFF FOREIGN KEY (election_id) REFERENCES election (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9E86507BEBB4B8AD FOREIGN KEY (voter_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9E86507BB0ADB74C ON election_registration (receipt_number)');
        $this->addSql('CREATE INDEX IDX_9E86507BA708DAFF ON election_registration (election_id)');
        $this->addSql('CREATE INDEX IDX_9E86507BEBB4B8AD ON election_registration (voter_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_election_voter ON election_registration (election_id, voter_id)');
        $this->addSql('CREATE TABLE manifesto (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(200) NOT NULL, summary CLOB DEFAULT NULL, is_published BOOLEAN NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, party_id INTEGER NOT NULL, CONSTRAINT FK_797528BC213C1059 FOREIGN KEY (party_id) REFERENCES political_party (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_797528BC213C1059 ON manifesto (party_id)');
        $this->addSql('CREATE TABLE manifesto_section (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(200) NOT NULL, content CLOB NOT NULL, position INTEGER DEFAULT 0 NOT NULL, manifesto_id INTEGER NOT NULL, CONSTRAINT FK_8B1D314DD737E924 FOREIGN KEY (manifesto_id) REFERENCES manifesto (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8B1D314DD737E924 ON manifesto_section (manifesto_id)');
        $this->addSql('CREATE TABLE political_party (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(160) NOT NULL, abbreviation VARCHAR(20) NOT NULL, description CLOB DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, email VARCHAR(160) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, motto VARCHAR(255) DEFAULT NULL, leader_name VARCHAR(160) NOT NULL, leader_title VARCHAR(160) DEFAULT NULL, leader_announced_at DATETIME DEFAULT NULL, registration_number VARCHAR(20) NOT NULL, status VARCHAR(16) NOT NULL, registered_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEABE1695E237E06 ON political_party (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEABE169BCF3411D ON political_party (abbreviation)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEABE16938CEDFBE ON political_party (registration_number)');
        $this->addSql('CREATE TABLE poll (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description CLOB DEFAULT NULL, is_survey BOOLEAN NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, requires_auth BOOLEAN NOT NULL, show_results_after_end BOOLEAN NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE poll_option (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, position INTEGER DEFAULT 0 NOT NULL, question_id INTEGER NOT NULL, CONSTRAINT FK_B68343EB1E27F6BF FOREIGN KEY (question_id) REFERENCES poll_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B68343EB1E27F6BF ON poll_option (question_id)');
        $this->addSql('CREATE TABLE poll_question (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title CLOB NOT NULL, type VARCHAR(10) NOT NULL, required BOOLEAN NOT NULL, position INTEGER DEFAULT 0 NOT NULL, poll_id INTEGER NOT NULL, CONSTRAINT FK_6D31FE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6D31FE3C947C0F ON poll_question (poll_id)');
        $this->addSql('CREATE TABLE poll_response (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, submitted_at DATETIME NOT NULL, poll_id INTEGER NOT NULL, voter_id INTEGER NOT NULL, CONSTRAINT FK_88E1734B3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_88E1734BEBB4B8AD FOREIGN KEY (voter_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_88E1734B3C947C0F ON poll_response (poll_id)');
        $this->addSql('CREATE INDEX IDX_88E1734BEBB4B8AD ON poll_response (voter_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_poll_voter ON poll_response (poll_id, voter_id)');
        $this->addSql('CREATE TABLE poll_selection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, response_id INTEGER NOT NULL, question_id INTEGER NOT NULL, option_id INTEGER NOT NULL, CONSTRAINT FK_5D722523FBF32840 FOREIGN KEY (response_id) REFERENCES poll_response (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5D7225231E27F6BF FOREIGN KEY (question_id) REFERENCES poll_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5D722523A7C41D6F FOREIGN KEY (option_id) REFERENCES poll_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5D722523FBF32840 ON poll_selection (response_id)');
        $this->addSql('CREATE INDEX IDX_5D7225231E27F6BF ON poll_selection (question_id)');
        $this->addSql('CREATE INDEX IDX_5D722523A7C41D6F ON poll_selection (option_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_response_question_option ON poll_selection (response_id, question_id, option_id)');
        $this->addSql('CREATE TABLE post (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_name VARCHAR(200) NOT NULL, content CLOB NOT NULL, is_removed BOOLEAN NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, thread_id INTEGER NOT NULL, author_id INTEGER DEFAULT NULL, CONSTRAINT FK_5A8A6C8DE2904019 FOREIGN KEY (thread_id) REFERENCES thread (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');
        $this->addSql('CREATE TABLE registration_token (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code_hash VARCHAR(64) NOT NULL, type VARCHAR(32) NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, attempts INTEGER DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, voter_id INTEGER NOT NULL, CONSTRAINT FK_D09D01D3EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D09D01D3EBB4B8AD ON registration_token (voter_id)');
        $this->addSql('CREATE TABLE round_table (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description CLOB NOT NULL, topic VARCHAR(255) DEFAULT NULL, scheduled_at DATETIME NOT NULL, ends_at DATETIME DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, is_online BOOLEAN NOT NULL, host_name VARCHAR(200) DEFAULT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, election_id INTEGER DEFAULT NULL, CONSTRAINT FK_205A9BBFA708DAFF FOREIGN KEY (election_id) REFERENCES election (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_205A9BBFA708DAFF ON round_table (election_id)');
        $this->addSql('CREATE TABLE round_table_registration (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, registered_at DATETIME NOT NULL, round_table_id INTEGER NOT NULL, voter_id INTEGER NOT NULL, CONSTRAINT FK_70917861B1489706 FOREIGN KEY (round_table_id) REFERENCES round_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_70917861EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_70917861B1489706 ON round_table_registration (round_table_id)');
        $this->addSql('CREATE INDEX IDX_70917861EBB4B8AD ON round_table_registration (voter_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_roundtable_voter ON round_table_registration (round_table_id, voter_id)');
        $this->addSql('CREATE TABLE thread (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category VARCHAR(16) NOT NULL, title VARCHAR(200) NOT NULL, slug VARCHAR(230) NOT NULL, content CLOB NOT NULL, author_name VARCHAR(200) NOT NULL, is_pinned BOOLEAN NOT NULL, is_locked BOOLEAN NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, author_id INTEGER DEFAULT NULL, election_id INTEGER DEFAULT NULL, party_id INTEGER DEFAULT NULL, round_table_id INTEGER DEFAULT NULL, CONSTRAINT FK_31204C83F675F31B FOREIGN KEY (author_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_31204C83A708DAFF FOREIGN KEY (election_id) REFERENCES election (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_31204C83213C1059 FOREIGN KEY (party_id) REFERENCES political_party (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_31204C83B1489706 FOREIGN KEY (round_table_id) REFERENCES round_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31204C83989D9B62 ON thread (slug)');
        $this->addSql('CREATE INDEX IDX_31204C83F675F31B ON thread (author_id)');
        $this->addSql('CREATE INDEX IDX_31204C83A708DAFF ON thread (election_id)');
        $this->addSql('CREATE INDEX IDX_31204C83213C1059 ON thread (party_id)');
        $this->addSql('CREATE INDEX IDX_31204C83B1489706 ON thread (round_table_id)');
        $this->addSql('CREATE TABLE vote (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cast_at DATETIME NOT NULL, election_id INTEGER NOT NULL, candidate_id INTEGER NOT NULL, voter_id INTEGER NOT NULL, CONSTRAINT FK_5A108564A708DAFF FOREIGN KEY (election_id) REFERENCES election (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5A10856491BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5A108564EBB4B8AD FOREIGN KEY (voter_id) REFERENCES voter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5A108564A708DAFF ON vote (election_id)');
        $this->addSql('CREATE INDEX IDX_5A10856491BD8781 ON vote (candidate_id)');
        $this->addSql('CREATE INDEX IDX_5A108564EBB4B8AD ON vote (voter_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_election_voter_vote ON vote (election_id, voter_id)');
        $this->addSql('CREATE TABLE voter (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL, national_id VARCHAR(20) NOT NULL, voter_number VARCHAR(12) NOT NULL, confirmation_code VARCHAR(20) DEFAULT NULL, first_name VARCHAR(80) NOT NULL, middle_name VARCHAR(80) DEFAULT NULL, last_name VARCHAR(80) NOT NULL, gender VARCHAR(1) NOT NULL, date_of_birth DATE NOT NULL, email VARCHAR(160) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, region VARCHAR(100) DEFAULT NULL, status VARCHAR(16) NOT NULL, confirmed_at DATETIME DEFAULT NULL, terms_accepted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, district_id INTEGER DEFAULT NULL, CONSTRAINT FK_268C4A59B08FA272 FOREIGN KEY (district_id) REFERENCES district (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_268C4A59F85E0677 ON voter (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_268C4A5936491297 ON voter (national_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_268C4A598662D0C8 ON voter (voter_number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_268C4A59A0E239DE ON voter (confirmation_code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_268C4A59E7927C74 ON voter (email)');
        $this->addSql('CREATE INDEX IDX_268C4A59B08FA272 ON voter (district_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE district');
        $this->addSql('DROP TABLE election');
        $this->addSql('DROP TABLE election_registration');
        $this->addSql('DROP TABLE manifesto');
        $this->addSql('DROP TABLE manifesto_section');
        $this->addSql('DROP TABLE political_party');
        $this->addSql('DROP TABLE poll');
        $this->addSql('DROP TABLE poll_option');
        $this->addSql('DROP TABLE poll_question');
        $this->addSql('DROP TABLE poll_response');
        $this->addSql('DROP TABLE poll_selection');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE registration_token');
        $this->addSql('DROP TABLE round_table');
        $this->addSql('DROP TABLE round_table_registration');
        $this->addSql('DROP TABLE thread');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE voter');
    }
}
