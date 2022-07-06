<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220706171501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reset_password_request (selector VARCHAR(255) NOT NULL, user_id INT DEFAULT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, hashed_token VARCHAR(255) NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(selector)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE reset_password_request');
    }
}
