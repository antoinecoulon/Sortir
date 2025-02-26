<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225085140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE event ADD state ENUM('CREATED', 'OPENED', 'CLOSED', 'PROCESSING', 'FINISHED', 'CANCELLED') NOT NULL");
        $this->addSql('ALTER TABLE event RENAME INDEX idx_3bae0aa7876c4dda TO IDX_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE user ADD pseudo VARCHAR(20) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_PSEUDO ON user (pseudo)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP state');
        $this->addSql('ALTER TABLE event RENAME INDEX idx_3bae0aa7a76ed395 TO IDX_3BAE0AA7876C4DDA');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_PSEUDO ON `user`');
        $this->addSql('ALTER TABLE `user` DROP pseudo');
    }
}
