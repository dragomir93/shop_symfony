<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211127141825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orders_products (id INT AUTO_INCREMENT NOT NULL, orders_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_749C879CCFFE9AD6 (orders_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orders_products ADD CONSTRAINT FK_749C879CCFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE articles ADD orders_products_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD31681F335533 FOREIGN KEY (orders_products_id) REFERENCES orders_products (id)');
        $this->addSql('CREATE INDEX IDX_BFDD31681F335533 ON articles (orders_products_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD31681F335533');
        $this->addSql('DROP TABLE orders_products');
        $this->addSql('DROP INDEX IDX_BFDD31681F335533 ON articles');
        $this->addSql('ALTER TABLE articles DROP orders_products_id');
    }
}
