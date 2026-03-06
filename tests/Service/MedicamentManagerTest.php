<?php

namespace App\Tests\Service;

use App\Entity\Medicament;
use App\Service\MedicamentManager;
use PHPUnit\Framework\TestCase;

class MedicamentManagerTest extends TestCase
{
    public function testMedicamentValide(): void
    {
        $medicament = new Medicament();
        $medicament->setNom('Doliprane');
        $medicament->setQuantiteStock(100);

        $manager = new MedicamentManager();
        $this->assertTrue($manager->validate($medicament));
    }

    public function testMedicamentSansNomRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du médicament est obligatoire.');

        $medicament = new Medicament();
        $medicament->setNom('');
        $medicament->setQuantiteStock(10);

        $manager = new MedicamentManager();
        $manager->validate($medicament);
    }

    public function testMedicamentStockNegatifRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La quantité en stock ne peut pas être négative.');

        $medicament = new Medicament();
        $medicament->setNom('Aspirine');
        $medicament->setQuantiteStock(-1);

        $manager = new MedicamentManager();
        $manager->validate($medicament);
    }
}
