<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250403082451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) NOT NULL, color VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, rarity VARCHAR(15) NOT NULL, is_legends_limited TINYINT(1) NOT NULL, is_zenkai TINYINT(1) NOT NULL, is_fusing TINYINT(1) NOT NULL, is_tag TINYINT(1) NOT NULL, is_transforming TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE unit
        SQL);
    }
}
