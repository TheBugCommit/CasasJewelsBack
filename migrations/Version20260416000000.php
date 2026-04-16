<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category and image_url columns to products table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE products ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'Ring'");
        $this->addSql("ALTER TABLE products ADD COLUMN image_url VARCHAR(500) DEFAULT NULL");
        $this->addSql("ALTER TABLE products ALTER COLUMN category DROP DEFAULT");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP COLUMN category');
        $this->addSql('ALTER TABLE products DROP COLUMN image_url');
    }
}
