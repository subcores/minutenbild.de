<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216131104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE images (id SERIAL NOT NULL, hash VARCHAR(255) NOT NULL, extension VARCHAR(255) NOT NULL, size INT NOT NULL, minutes SMALLINT DEFAULT NULL, password TEXT DEFAULT NULL, message TEXT DEFAULT NULL, ip TEXT NOT NULL, is_active BOOLEAN NOT NULL, is_timeout BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_hash ON images (hash)');
        $this->addSql('CREATE INDEX idx_active ON images (is_active)');
        $this->addSql('COMMENT ON COLUMN images.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN images.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE images');
    }
}
