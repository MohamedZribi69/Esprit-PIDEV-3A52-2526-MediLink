<?php

namespace App\Service;

use App\Entity\Ordonnance;

/**
 * Service métier pour la gestion des ordonnances.
 * Règles métier :
 * 1. Le médecin et le patient sont obligatoires.
 * 2. L'ordonnance doit contenir au moins un médicament.
 */
final class OrdonnanceManager
{
    /**
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Ordonnance $ordonnance): bool
    {
        if ($ordonnance->getMedecin() === null) {
            throw new \InvalidArgumentException('Le médecin est obligatoire.');
        }

        if ($ordonnance->getPatient() === null) {
            throw new \InvalidArgumentException('Le patient est obligatoire.');
        }

        if ($ordonnance->getOrdonnanceMedicaments()->isEmpty()) {
            throw new \InvalidArgumentException('L\'ordonnance doit contenir au moins un médicament.');
        }

        return true;
    }
}
