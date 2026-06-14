<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614073159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, name VARCHAR(150) NOT NULL, organization_id INT NOT NULL, UNIQUE INDEX UNIQ_B8EE3872B5B48B91 (public_id), INDEX IDX_B8EE387232C8A3DE (organization_id), UNIQUE INDEX uniq_club_organization_name (organization_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE club_membership_group (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, name VARCHAR(512) NOT NULL, description LONGTEXT DEFAULT NULL, club_id INT NOT NULL, organization_id INT NOT NULL, UNIQUE INDEX UNIQ_798929B5B48B91 (public_id), INDEX IDX_79892961190A32 (club_id), INDEX IDX_79892932C8A3DE (organization_id), UNIQUE INDEX uniq_cmg_name_club_id (name, club_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE club_membership_group_membership (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, notes LONGTEXT DEFAULT NULL, group_id INT NOT NULL, membership_id INT NOT NULL, UNIQUE INDEX UNIQ_5E75C4EAB5B48B91 (public_id), INDEX IDX_5E75C4EAFE54D947 (group_id), INDEX IDX_5E75C4EA1FB354CD (membership_id), UNIQUE INDEX uniq_cmgm_membership_group (membership_id, group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE connection_user (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password_hash VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, activated_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4B83D173B5B48B91 (public_id), UNIQUE INDEX UNIQ_4B83D173E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE interclub_membership_group (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, name VARCHAR(512) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_CC680D9AB5B48B91 (public_id), UNIQUE INDEX UNIQ_CC680D9A5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE interclub_membership_group_membership (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, notes LONGTEXT DEFAULT NULL, group_id INT NOT NULL, membership_id INT NOT NULL, UNIQUE INDEX UNIQ_E1407159B5B48B91 (public_id), INDEX IDX_E1407159FE54D947 (group_id), INDEX IDX_E14071591FB354CD (membership_id), INDEX idx_imgm_membership_group (membership_id, group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE membership (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, joined_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, person_id INT NOT NULL, club_id INT NOT NULL, organization_id INT NOT NULL, UNIQUE INDEX UNIQ_86FFD285B5B48B91 (public_id), INDEX IDX_86FFD285217BBB47 (person_id), INDEX IDX_86FFD28561190A32 (club_id), INDEX IDX_86FFD28532C8A3DE (organization_id), INDEX idx_membership_person_club_ended_at (person_id, club_id, ended_at), INDEX idx_membership_organization_club (organization_id, club_id), INDEX idx_membership_organization_person (organization_id, person_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE organization (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, name VARCHAR(150) NOT NULL, slug VARCHAR(120) NOT NULL, enabled TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, service_plan VARCHAR(255) DEFAULT \'community\' NOT NULL, UNIQUE INDEX UNIQ_C1EE637CB5B48B91 (public_id), UNIQUE INDEX UNIQ_C1EE637C5E237E06 (name), UNIQUE INDEX UNIQ_C1EE637C989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE organization_user (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, roles JSON NOT NULL, enabled TINYINT NOT NULL, created_at DATETIME NOT NULL, connection_user_id INT NOT NULL, organization_id INT NOT NULL, person_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B49AE8D4B5B48B91 (public_id), INDEX IDX_B49AE8D4DC6435B6 (connection_user_id), INDEX IDX_B49AE8D432C8A3DE (organization_id), INDEX IDX_B49AE8D4217BBB47 (person_id), UNIQUE INDEX uniq_organization_user_connection_user_organization (connection_user_id, organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, firstname VARCHAR(150) NOT NULL, lastname VARCHAR(150) NOT NULL, email VARCHAR(180) DEFAULT NULL, organization_id INT NOT NULL, UNIQUE INDEX UNIQ_34DCD176B5B48B91 (public_id), INDEX IDX_34DCD17632C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE person_contact (id INT AUTO_INCREMENT NOT NULL, public_id VARCHAR(26) NOT NULL, type VARCHAR(255) NOT NULL, is_emergency_contact TINYINT DEFAULT 0 NOT NULL, person_id INT NOT NULL, contact_person_id INT NOT NULL, UNIQUE INDEX UNIQ_6EFC55B1B5B48B91 (public_id), INDEX IDX_6EFC55B1217BBB47 (person_id), INDEX IDX_6EFC55B14F8A983C (contact_person_id), INDEX idx_person_contact_subject_type (person_id, type), UNIQUE INDEX uniq_person_contact (person_id, contact_person_id, type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, token_hash VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, revocation_reason VARCHAR(64) DEFAULT NULL, user_agent VARCHAR(512) DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, mode VARCHAR(16) NOT NULL, connection_user_id INT NOT NULL, replaced_by_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_C74F2195B3BC57DA (token_hash), INDEX IDX_C74F2195DC6435B6 (connection_user_id), INDEX IDX_C74F21959AC69B54 (replaced_by_id), INDEX idx_refresh_token_expires_at (expires_at), INDEX idx_refresh_token_revoked_at (revoked_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE387232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE club_membership_group ADD CONSTRAINT FK_79892961190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE club_membership_group ADD CONSTRAINT FK_79892932C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE club_membership_group_membership ADD CONSTRAINT FK_5E75C4EAFE54D947 FOREIGN KEY (group_id) REFERENCES club_membership_group (id)');
        $this->addSql('ALTER TABLE club_membership_group_membership ADD CONSTRAINT FK_5E75C4EA1FB354CD FOREIGN KEY (membership_id) REFERENCES membership (id)');
        $this->addSql('ALTER TABLE interclub_membership_group_membership ADD CONSTRAINT FK_E1407159FE54D947 FOREIGN KEY (group_id) REFERENCES interclub_membership_group (id)');
        $this->addSql('ALTER TABLE interclub_membership_group_membership ADD CONSTRAINT FK_E14071591FB354CD FOREIGN KEY (membership_id) REFERENCES membership (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD28561190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD28532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE organization_user ADD CONSTRAINT FK_B49AE8D4DC6435B6 FOREIGN KEY (connection_user_id) REFERENCES connection_user (id)');
        $this->addSql('ALTER TABLE organization_user ADD CONSTRAINT FK_B49AE8D432C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE organization_user ADD CONSTRAINT FK_B49AE8D4217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17632C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE person_contact ADD CONSTRAINT FK_6EFC55B1217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person_contact ADD CONSTRAINT FK_6EFC55B14F8A983C FOREIGN KEY (contact_person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195DC6435B6 FOREIGN KEY (connection_user_id) REFERENCES connection_user (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F21959AC69B54 FOREIGN KEY (replaced_by_id) REFERENCES refresh_token (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE387232C8A3DE');
        $this->addSql('ALTER TABLE club_membership_group DROP FOREIGN KEY FK_79892961190A32');
        $this->addSql('ALTER TABLE club_membership_group DROP FOREIGN KEY FK_79892932C8A3DE');
        $this->addSql('ALTER TABLE club_membership_group_membership DROP FOREIGN KEY FK_5E75C4EAFE54D947');
        $this->addSql('ALTER TABLE club_membership_group_membership DROP FOREIGN KEY FK_5E75C4EA1FB354CD');
        $this->addSql('ALTER TABLE interclub_membership_group_membership DROP FOREIGN KEY FK_E1407159FE54D947');
        $this->addSql('ALTER TABLE interclub_membership_group_membership DROP FOREIGN KEY FK_E14071591FB354CD');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285217BBB47');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28561190A32');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28532C8A3DE');
        $this->addSql('ALTER TABLE organization_user DROP FOREIGN KEY FK_B49AE8D4DC6435B6');
        $this->addSql('ALTER TABLE organization_user DROP FOREIGN KEY FK_B49AE8D432C8A3DE');
        $this->addSql('ALTER TABLE organization_user DROP FOREIGN KEY FK_B49AE8D4217BBB47');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17632C8A3DE');
        $this->addSql('ALTER TABLE person_contact DROP FOREIGN KEY FK_6EFC55B1217BBB47');
        $this->addSql('ALTER TABLE person_contact DROP FOREIGN KEY FK_6EFC55B14F8A983C');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195DC6435B6');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F21959AC69B54');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE club_membership_group');
        $this->addSql('DROP TABLE club_membership_group_membership');
        $this->addSql('DROP TABLE connection_user');
        $this->addSql('DROP TABLE interclub_membership_group');
        $this->addSql('DROP TABLE interclub_membership_group_membership');
        $this->addSql('DROP TABLE membership');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE organization_user');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_contact');
        $this->addSql('DROP TABLE refresh_token');
    }
}
