<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261118092035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_preferences (user_id INT NOT NULL, preference_id INT NOT NULL, INDEX IDX_402A6F60A76ED395 (user_id), INDEX IDX_402A6F60D81022C0 (preference_id), PRIMARY KEY (user_id, preference_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_preferences ADD CONSTRAINT FK_402A6F60A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_preferences ADD CONSTRAINT FK_402A6F60D81022C0 FOREIGN KEY (preference_id) REFERENCES preference (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_preferences DROP FOREIGN KEY FK_402A6F60A76ED395');
        $this->addSql('ALTER TABLE user_preferences DROP FOREIGN KEY FK_402A6F60D81022C0');
        $this->addSql('DROP TABLE user_preferences');
    }
}
