<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415131705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_in_order_item_group (id INT AUTO_INCREMENT NOT NULL, product INT NOT NULL, group_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_5CF3D98CD34A04AD (product), INDEX IDX_5CF3D98CFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_in_order_item_group ADD CONSTRAINT FK_5CF3D98CD34A04AD FOREIGN KEY (product) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_in_order_item_group ADD CONSTRAINT FK_5CF3D98CFE54D947 FOREIGN KEY (group_id) REFERENCES order_item (id)');
        $this->addSql('ALTER TABLE order_item ADD price DOUBLE PRECISION DEFAULT NULL, ADD vat_rate INT DEFAULT NULL, ADD is_group TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_in_order_item_group DROP FOREIGN KEY FK_5CF3D98CD34A04AD');
        $this->addSql('ALTER TABLE product_in_order_item_group DROP FOREIGN KEY FK_5CF3D98CFE54D947');
        $this->addSql('DROP TABLE product_in_order_item_group');
        $this->addSql('ALTER TABLE order_item DROP price, DROP vat_rate, DROP is_group');
    }
}
