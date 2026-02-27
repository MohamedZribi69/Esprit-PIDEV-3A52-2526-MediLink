<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260227180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ verification_token sur user pour la vérification de compte.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `user` ADD verification_token VARCHAR(64) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `user` DROP COLUMN verification_token");
    }
}

