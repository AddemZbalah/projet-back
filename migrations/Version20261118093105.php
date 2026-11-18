<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261118093105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE offer_preferences (offer_id INT NOT NULL, preference_id INT NOT NULL, INDEX IDX_68131AFB53C674EE (offer_id), INDEX IDX_68131AFBD81022C0 (preference_id), PRIMARY KEY (offer_id, preference_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE offer_preferences ADD CONSTRAINT FK_68131AFB53C674EE FOREIGN KEY (offer_id) REFERENCES offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offer_preferences ADD CONSTRAINT FK_68131AFBD81022C0 FOREIGN KEY (preference_id) REFERENCES preference (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer_preferences DROP FOREIGN KEY FK_68131AFB53C674EE');
        $this->addSql('ALTER TABLE offer_preferences DROP FOREIGN KEY FK_68131AFBD81022C0');
        $this->addSql('DROP TABLE offer_preferences');
    }
}
