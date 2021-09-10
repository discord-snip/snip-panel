<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210908112243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__snippet AS SELECT id, language_id, name, code FROM snippet');
        $this->addSql('DROP TABLE snippet');
        $this->addSql('CREATE TABLE snippet (id BLOB NOT NULL --(DC2Type:uuid)
        , language_id BLOB NOT NULL --(DC2Type:uuid)
        , name VARCHAR(200) NOT NULL COLLATE BINARY, code VARCHAR(1000) NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_961C8CD582F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO snippet (id, language_id, name, code) SELECT id, language_id, name, code FROM __temp__snippet');
        $this->addSql('DROP TABLE __temp__snippet');
        $this->addSql('CREATE INDEX IDX_961C8CD582F1BAF4 ON snippet (language_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_961C8CD582F1BAF4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__snippet AS SELECT id, language_id, name, code FROM snippet');
        $this->addSql('DROP TABLE snippet');
        $this->addSql('CREATE TABLE snippet (id BLOB NOT NULL --(DC2Type:uuid)
        , language_id BLOB NOT NULL --(DC2Type:uuid)
        , name VARCHAR(200) NOT NULL, code VARCHAR(1000) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO snippet (id, language_id, name, code) SELECT id, language_id, name, code FROM __temp__snippet');
        $this->addSql('DROP TABLE __temp__snippet');
        $this->addSql('CREATE INDEX IDX_961C8CD582F1BAF4 ON snippet (language_id)');
    }
}
