<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505141326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` MODIFY `creation_date` DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `order` MODIFY `update_date` DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` MODIFY `creation_date` DATE NOT NULL');
        $this->addSql('ALTER TABLE `order` MODIFY `update_date` DATE NOT NULL');
    }
}
