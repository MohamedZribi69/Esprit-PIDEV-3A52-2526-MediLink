<?php

namespace App\Service;

use App\Entity\Medicament;

/**
 * Service métier pour la gestion des médicaments.
 * Règles métier :
 * 1. Le nom du médicament est obligatoire (non vide).
 * 2. La quantité en stock ne peut pas être négative.
 */
final class MedicamentManager
{
    /**
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(Medicament $medicament): bool
    {
        $nom = $medicament->getNom();
        if ($nom === null || trim($nom) === '') {
            throw new \InvalidArgumentException('Le nom du médicament est obligatoire.');
        }

        if ($medicament->getQuantiteStock() < 0) {
            throw new \InvalidArgumentException('La quantité en stock ne peut pas être négative.');
        }

        return true;
    }
}
