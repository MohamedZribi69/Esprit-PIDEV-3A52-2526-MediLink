<?php

namespace App\Service;

use App\Entity\RendezVous;

/**
 * Service métier pour la gestion des rendez-vous.
 * Règles métier :
 * 1. La date/heure du rendez-vous ne peut pas être dans le passé.
 * 2. Le statut doit être parmi les valeurs autorisées.
 */
final class RendezVousManager
{
    private const STATUTS_VALIDES = [
        RendezVous::STATUT_EN_ATTENTE,
        RendezVous::STATUT_CONFIRME,
        RendezVous::STATUT_ANNULE,
        RendezVous::STATUT_TERMINE,
    ];

    /**
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(RendezVous $rendezVous): bool
    {
        $dateHeure = $rendezVous->getDateHeure();
        if ($dateHeure !== null && $dateHeure < new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('La date et l\'heure du rendez-vous ne peuvent pas être dans le passé.');
        }

        $statut = $rendezVous->getStatut();
        if (!in_array($statut, self::STATUTS_VALIDES, true)) {
            throw new \InvalidArgumentException('Le statut du rendez-vous est invalide.');
        }

        return true;
    }
}
