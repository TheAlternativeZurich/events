<?php

declare(strict_types=1);

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201007090614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendance (id CHAR(36) NOT NULL --(DC2Type:guid)
        , event_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , registration_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , join_date DATETIME NOT NULL, leave_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, phone CLOB NOT NULL, email CLOB NOT NULL, street_address CLOB NOT NULL, postal_code INTEGER NOT NULL, locality CLOB NOT NULL, canton VARCHAR(255) DEFAULT NULL, country CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6DE30D9171F7E88B ON attendance (event_id)');
        $this->addSql('CREATE INDEX IDX_6DE30D91833D8F43 ON attendance (registration_id)');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP INDEX IDX_62A8A7A771F7E88B');
        $this->addSql('DROP INDEX IDX_62A8A7A7A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__registration AS SELECT id, event_id, user_id, is_organizer, number, created_at, last_changed_at, given_name, family_name, phone, email, street_address, postal_code, locality, canton, country FROM registration');
        $this->addSql('DROP TABLE registration');
        $this->addSql('CREATE TABLE registration (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , event_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , is_organizer BOOLEAN NOT NULL, number INTEGER NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, given_name CLOB NOT NULL COLLATE BINARY, family_name CLOB NOT NULL COLLATE BINARY, phone CLOB NOT NULL COLLATE BINARY, email CLOB NOT NULL COLLATE BINARY, street_address CLOB NOT NULL COLLATE BINARY, postal_code INTEGER NOT NULL, locality CLOB NOT NULL COLLATE BINARY, canton VARCHAR(255) DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_62A8A7A771F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_62A8A7A7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO registration (id, event_id, user_id, is_organizer, number, created_at, last_changed_at, given_name, family_name, phone, email, street_address, postal_code, locality, canton, country) SELECT id, event_id, user_id, is_organizer, number, created_at, last_changed_at, given_name, family_name, phone, email, street_address, postal_code, locality, canton, country FROM __temp__registration');
        $this->addSql('DROP TABLE __temp__registration');
        $this->addSql('CREATE INDEX IDX_62A8A7A771F7E88B ON registration (event_id)');
        $this->addSql('CREATE INDEX IDX_62A8A7A7A76ED395 ON registration (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participation (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , event_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , registration_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:guid)
        , join_date DATETIME NOT NULL, leave_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, given_name CLOB NOT NULL COLLATE BINARY, family_name CLOB NOT NULL COLLATE BINARY, phone CLOB NOT NULL COLLATE BINARY, email CLOB NOT NULL COLLATE BINARY, street_address CLOB NOT NULL COLLATE BINARY, postal_code INTEGER NOT NULL, locality CLOB NOT NULL COLLATE BINARY, canton VARCHAR(255) DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB55E24F71F7E88B ON participation (event_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24F833D8F43 ON participation (registration_id)');
        $this->addSql('DROP TABLE attendance');
        $this->addSql('DROP INDEX IDX_62A8A7A771F7E88B');
        $this->addSql('DROP INDEX IDX_62A8A7A7A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__registration AS SELECT id, event_id, user_id, is_organizer, number, created_at, last_changed_at, given_name, family_name, phone, email, street_address, postal_code, locality, canton, country FROM registration');
        $this->addSql('DROP TABLE registration');
        $this->addSql('CREATE TABLE registration (id CHAR(36) NOT NULL --(DC2Type:guid)
        , event_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , user_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , is_organizer BOOLEAN NOT NULL, number INTEGER NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, phone CLOB NOT NULL, email CLOB NOT NULL, street_address CLOB NOT NULL, postal_code INTEGER NOT NULL, locality CLOB NOT NULL, canton VARCHAR(255) DEFAULT NULL, country CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO registration (id, event_id, user_id, is_organizer, number, created_at, last_changed_at, given_name, family_name, phone, email, street_address, postal_code, locality, canton, country) SELECT id, event_id, user_id, is_organizer, number, created_at, last_changed_at, given_name, family_name, phone, email, street_address, postal_code, locality, canton, country FROM __temp__registration');
        $this->addSql('DROP TABLE __temp__registration');
        $this->addSql('CREATE INDEX IDX_62A8A7A771F7E88B ON registration (event_id)');
        $this->addSql('CREATE INDEX IDX_62A8A7A7A76ED395 ON registration (user_id)');
    }
}
