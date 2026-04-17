<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add preparation_days and shipping_days columns to products table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE products ADD COLUMN preparation_days INTEGER NOT NULL DEFAULT 0");
        $this->addSql("ALTER TABLE products ADD COLUMN shipping_days INTEGER NOT NULL DEFAULT 0");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP COLUMN preparation_days');
        $this->addSql('ALTER TABLE products DROP COLUMN shipping_days');
    }
}
