<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319222640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, name VARCHAR(50) NOT NULL, code INT NOT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_64C19C1979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(255) DEFAULT NULL, company_id VARCHAR(255) DEFAULT NULL, creation_date DATETIME DEFAULT NULL, bank_account VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_user (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, user_id INT NOT NULL, roles JSON NOT NULL, INDEX IDX_CEFECCA7979B1AD6 (company_id), INDEX IDX_CEFECCA7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dining_table (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, description VARCHAR(100) DEFAULT NULL, number INT NOT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_2553802979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, dining_table_id INT NOT NULL, company_id INT NOT NULL, description VARCHAR(255) DEFAULT NULL, inventory_number INT NOT NULL, creation_date DATE NOT NULL, update_date DATE NOT NULL, INDEX IDX_F5299398695B08DC (dining_table_id), INDEX IDX_F5299398979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, order_id INT NOT NULL, quantity INT NOT NULL, is_paid TINYINT(1) NOT NULL, INDEX IDX_52EA1F094584665A (product_id), INDEX IDX_52EA1F098D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_item_payment (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, payment_id INT NOT NULL, paid_quantity INT NOT NULL, INDEX IDX_62B53633E415FB15 (order_item_id), INDEX IDX_62B536334C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, paymentTime DATETIME NOT NULL, paymentMethod VARCHAR(50) NOT NULL, INDEX IDX_6D28840D8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, company_id INT NOT NULL, name VARCHAR(50) NOT NULL, inventory_number INT NOT NULL, creation_date DATE NOT NULL, update_date DATE NOT NULL, price DOUBLE PRECISION DEFAULT NULL, vat_rate INT DEFAULT NULL, is_group TINYINT(1) NOT NULL, is_deleted TINYINT(1) NOT NULL, manufacturer VARCHAR(50) DEFAULT NULL, description VARCHAR(50) DEFAULT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), INDEX IDX_D34A04AD979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_in_group (id INT AUTO_INCREMENT NOT NULL, product INT NOT NULL, group_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_EC85302CD34A04AD (product), INDEX IDX_EC85302CFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, registration_date DATETIME DEFAULT NULL, last_logon DATETIME DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_user ADD CONSTRAINT FK_CEFECCA7979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company_user ADD CONSTRAINT FK_CEFECCA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dining_table ADD CONSTRAINT FK_2553802979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398695B08DC FOREIGN KEY (dining_table_id) REFERENCES dining_table (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item_payment ADD CONSTRAINT FK_62B53633E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id)');
        $this->addSql('ALTER TABLE order_item_payment ADD CONSTRAINT FK_62B536334C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE product_in_group ADD CONSTRAINT FK_EC85302CD34A04AD FOREIGN KEY (product) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_in_group ADD CONSTRAINT FK_EC85302CFE54D947 FOREIGN KEY (group_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1979B1AD6');
        $this->addSql('ALTER TABLE company_user DROP FOREIGN KEY FK_CEFECCA7979B1AD6');
        $this->addSql('ALTER TABLE company_user DROP FOREIGN KEY FK_CEFECCA7A76ED395');
        $this->addSql('ALTER TABLE dining_table DROP FOREIGN KEY FK_2553802979B1AD6');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398695B08DC');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398979B1AD6');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_item_payment DROP FOREIGN KEY FK_62B53633E415FB15');
        $this->addSql('ALTER TABLE order_item_payment DROP FOREIGN KEY FK_62B536334C3A3BB');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D8D9F6D38');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD979B1AD6');
        $this->addSql('ALTER TABLE product_in_group DROP FOREIGN KEY FK_EC85302CD34A04AD');
        $this->addSql('ALTER TABLE product_in_group DROP FOREIGN KEY FK_EC85302CFE54D947');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_user');
        $this->addSql('DROP TABLE dining_table');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE order_item_payment');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_in_group');
        $this->addSql('DROP TABLE user');
    }
}
