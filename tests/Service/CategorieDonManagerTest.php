<?php

namespace App\Tests\Service;

use App\Entity\CategorieDon;
use App\Service\CategorieDonManager;
use PHPUnit\Framework\TestCase;

class CategorieDonManagerTest extends TestCase
{
    public function testCategorieDonValide(): void
    {
        $categorie = new CategorieDon();
        $categorie->setNom('Médicaments');
        $categorie->setCouleur('#3498db');

        $manager = new CategorieDonManager();
        $this->assertTrue($manager->validate($categorie));
    }

    public function testCategorieDonSansNomRejetee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom de la catégorie est obligatoire.');

        $categorie = new CategorieDon();
        $categorie->setNom('');
        $categorie->setCouleur('#fff');

        $manager = new CategorieDonManager();
        $manager->validate($categorie);
    }

    public function testCategorieDonCouleurInvalideRejetee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La couleur doit être au format hexadécimal');

        $categorie = new CategorieDon();
        $categorie->setNom('Test');
        $categorie->setCouleur('rouge');

        $manager = new CategorieDonManager();
        $manager->validate($categorie);
    }
}
