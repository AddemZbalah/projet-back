<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261021071955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE offre (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, original_price NUMERIC(10, 2) NOT NULL, discounted_price NUMERIC(10, 2) NOT NULL, quantity_total INT NOT NULL, quantity_available INT NOT NULL, pickup_start DATETIME NOT NULL, pickup_end DATETIME NOT NULL, created_at DATETIME NOT NULL, status LONGTEXT NOT NULL, commerce_id INT NOT NULL, INDEX IDX_AF86866FB09114B7 (commerce_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FB09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE commerce CHANGE owner_id owner_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FB09114B7');
        $this->addSql('DROP TABLE offre');
        $this->addSql('ALTER TABLE commerce CHANGE owner_id owner_id INT DEFAULT NULL');
    }
}
