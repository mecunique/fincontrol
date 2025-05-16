<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250514090341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les tables user, category, transaction avec gestion des fuseaux horaires.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE category (
                id SERIAL NOT NULL,
                user_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_64C19C1A76ED395 ON category (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE transaction (
                id SERIAL NOT NULL,
                category_id INT NOT NULL,
                user_id INT NOT NULL,
                amount DOUBLE PRECISION NOT NULL,
                date DATETIME NOT NULL,
                timezone VARCHAR(64) NOT NULL,
                description TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D112469DE2 ON transaction (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D1A76ED395 ON transaction (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (
                id SERIAL NOT NULL,
                username VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
                id BIGSERIAL NOT NULL,
                body TEXT NOT NULL,
                headers TEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL,
                available_at DATETIME NOT NULL,
                delivered_at DATETIME DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        // Commentaires supprimés : PostgreSQL only
        // Triggers aussi supprimés pour compatibilité avec MySQL

        $this->addSql(<<<'SQL'
            ALTER TABLE category
                ADD CONSTRAINT FK_64C19C1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction
                ADD CONSTRAINT FK_723705D112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction
                ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
