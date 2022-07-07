<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220707090003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article_category CHANGE article_category_id category_id INT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article_category CHANGE category_id article_category_id INT');
    }
}
