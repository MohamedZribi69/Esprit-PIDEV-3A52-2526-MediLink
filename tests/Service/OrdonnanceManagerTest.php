<?php

namespace App\Tests\Service;

use App\Entity\Ordonnance;
use App\Entity\OrdonnanceMedicament;
use App\Entity\User;
use App\Service\OrdonnanceManager;
use PHPUnit\Framework\TestCase;

class OrdonnanceManagerTest extends TestCase
{
    public function testOrdonnanceValide(): void
    {
        $ordonnance = new Ordonnance();
        $ordonnance->setMedecin(new User());
        $ordonnance->setPatient(new User());
        $ordonnance->addOrdonnanceMedicament(new OrdonnanceMedicament());

        $manager = new OrdonnanceManager();
        $this->assertTrue($manager->validate($ordonnance));
    }

    public function testOrdonnanceSansMedecinRejetee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le médecin est obligatoire.');

        $ordonnance = new Ordonnance();
        $ordonnance->setMedecin(null);
        $ordonnance->setPatient(new User());
        $ordonnance->addOrdonnanceMedicament(new OrdonnanceMedicament());

        $manager = new OrdonnanceManager();
        $manager->validate($ordonnance);
    }

    public function testOrdonnanceSansMedicamentRejetee(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('au moins un médicament');

        $ordonnance = new Ordonnance();
        $ordonnance->setMedecin(new User());
        $ordonnance->setPatient(new User());

        $manager = new OrdonnanceManager();
        $manager->validate($ordonnance);
    }
}
