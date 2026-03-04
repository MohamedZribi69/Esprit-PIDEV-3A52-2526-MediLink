<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les colonnes preferred_time et max_days_ahead à la table user (merge feature gestion-rendezvous).
 */
final class Version20260303100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add preferred_time and max_days_ahead to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD preferred_time VARCHAR(20) DEFAULT NULL, ADD max_days_ahead INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP preferred_time, DROP max_days_ahead');
    }
}
