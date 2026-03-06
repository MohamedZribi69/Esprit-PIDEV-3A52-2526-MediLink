<?php

namespace App\Tests\Service;

use App\Entity\CategorieDon;
use App\Entity\Dons;
use App\Service\DonManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires du service DonManager.
 * Valide les règles métier : quantité > 0, date d'expiration future ou vide.
 */
class DonManagerTest extends TestCase
{
    public function testDonValide(): void
    {
        $don = new Dons();
        $don->setCategorie(new CategorieDon());
        $don->setArticleDescription('Médicaments');
        $don->setQuantite(5);
        $don->setDateExpiration(null);

        $manager = new DonManager();
        $this->assertTrue($manager->validate($don));
    }

    public function testDonQuantiteNulleRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La quantité doit être strictement positive.');

        $don = new Dons();
        $don->setCategorie(new CategorieDon());
        $don->setArticleDescription('Test');
        $don->setQuantite(0);
        $don->setDateExpiration(null);

        $manager = new DonManager();
        $manager->validate($don);
    }

    public function testDonDateExpirationPasseeRejetee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date d\'expiration doit être postérieure à aujourd\'hui.');

        $don = new Dons();
        $don->setCategorie(new CategorieDon());
        $don->setArticleDescription('Test');
        $don->setQuantite(2);
        $don->setDateExpiration(new \DateTimeImmutable('yesterday'));

        $manager = new DonManager();
        $manager->validate($don);
    }
}
