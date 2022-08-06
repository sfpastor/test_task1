<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220801143612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
            name VARCHAR(64) NOT NULL, 
            email VARCHAR(256) NOT NULL, 
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
            deleted DATETIME DEFAULT NULL, 
            notes CLOB DEFAULT NULL)
        ');
        $this->addSql('CREATE UNIQUE INDEX user_name_unique ON user (name)');
        $this->addSql('CREATE UNIQUE INDEX user_email_unique ON user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX user_name_unique');
        $this->addSql('DROP INDEX user_email_unique');
        $this->addSql('DROP TABLE user');
    }
}
