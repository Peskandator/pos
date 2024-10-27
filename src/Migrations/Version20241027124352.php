<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027124352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_64C19C1979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, company_id INT NOT NULL, name VARCHAR(50) NOT NULL, inventory_number INT NOT NULL, creation_date DATE NOT NULL, update_date DATE NOT NULL, price DOUBLE PRECISION DEFAULT NULL, vat_rate INT DEFAULT NULL, is_group TINYINT(1) NOT NULL, is_deleted TINYINT(1) NOT NULL, manufacturer VARCHAR(50) DEFAULT NULL, description VARCHAR(50) DEFAULT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), INDEX IDX_D34A04AD979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE productgroup_product (group_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_82B51063FE54D947 (group_id), INDEX IDX_82B510634584665A (product_id), PRIMARY KEY(group_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE productgroup_product ADD CONSTRAINT FK_82B51063FE54D947 FOREIGN KEY (group_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE productgroup_product ADD CONSTRAINT FK_82B510634584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1979B1AD6');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
        $this->addSql('ALTER TABLE productgroup_product DROP FOREIGN KEY FK_82B51063FE54D947');
        $this->addSql('ALTER TABLE productgroup_product DROP FOREIGN KEY FK_82B510634584665A');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE productgroup_product');
    }
}
