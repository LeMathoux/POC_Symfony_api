<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010095659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE editors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_games (id INT AUTO_INCREMENT NOT NULL, editor_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_704122936995AC4C (editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_game_category (video_game_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_A672CAD716230A8 (video_game_id), INDEX IDX_A672CAD712469DE2 (category_id), PRIMARY KEY(video_game_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_games ADD CONSTRAINT FK_704122936995AC4C FOREIGN KEY (editor_id) REFERENCES editors (id)');
        $this->addSql('ALTER TABLE video_game_category ADD CONSTRAINT FK_A672CAD716230A8 FOREIGN KEY (video_game_id) REFERENCES video_games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_game_category ADD CONSTRAINT FK_A672CAD712469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_games DROP FOREIGN KEY FK_704122936995AC4C');
        $this->addSql('ALTER TABLE video_game_category DROP FOREIGN KEY FK_A672CAD716230A8');
        $this->addSql('ALTER TABLE video_game_category DROP FOREIGN KEY FK_A672CAD712469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE editors');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE video_games');
        $this->addSql('DROP TABLE video_game_category');
    }
}
