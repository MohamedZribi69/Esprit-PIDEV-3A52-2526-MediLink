<?php

namespace App\Tests\Service;

use App\Entity\Disponibilite;
use App\Entity\RendezVous;
use App\Service\RendezVousManager;
use PHPUnit\Framework\TestCase;

class RendezVousManagerTest extends TestCase
{
    public function testRendezVousValide(): void
    {
        $dispo = new Disponibilite();
        $rdv = new RendezVous();
        $rdv->setDisponibilite($dispo);
        $rdv->setDateHeure(new \DateTimeImmutable('tomorrow noon'));
        $rdv->setStatut(RendezVous::STATUT_EN_ATTENTE);

        $manager = new RendezVousManager();
        $this->assertTrue($manager->validate($rdv));
    }

    public function testRendezVousDatePasseeRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ne peuvent pas être dans le passé');

        $dispo = new Disponibilite();
        $rdv = new RendezVous();
        $rdv->setDisponibilite($dispo);
        $rdv->setDateHeure(new \DateTimeImmutable('yesterday'));
        $rdv->setStatut(RendezVous::STATUT_EN_ATTENTE);

        $manager = new RendezVousManager();
        $manager->validate($rdv);
    }

    public function testRendezVousStatutInvalideRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut du rendez-vous est invalide.');

        $dispo = new Disponibilite();
        $rdv = new RendezVous();
        $rdv->setDisponibilite($dispo);
        $rdv->setDateHeure(new \DateTimeImmutable('tomorrow'));
        $rdv->setStatut('statut_inconnu');

        $manager = new RendezVousManager();
        $manager->validate($rdv);
    }
}
