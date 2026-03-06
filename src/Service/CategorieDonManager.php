<?php

namespace App\Service;

use App\Entity\CategorieDon;

/**
 * Service métier pour la gestion des catégories de dons.
 * Règles métier :
 * 1. Le nom de la catégorie est obligatoire (non vide).
 * 2. La couleur doit être au format hexadécimal (#xxx ou #xxxxxx).
 */
final class CategorieDonManager
{
    /**
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(CategorieDon $categorie): bool
    {
        $nom = $categorie->getNom();
        if ($nom === null || trim($nom) === '') {
            throw new \InvalidArgumentException('Le nom de la catégorie est obligatoire.');
        }

        $couleur = $categorie->getCouleur();
        if ($couleur !== '' && !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $couleur)) {
            throw new \InvalidArgumentException('La couleur doit être au format hexadécimal (#xxx ou #xxxxxx).');
        }

        return true;
    }
}
