<?php

namespace App\Service;

use App\Entity\User;

/**
 * Service métier pour la gestion des utilisateurs.
 * Règles métier :
 * 1. L'email doit être valide (format).
 * 2. Le statut doit être ACTIVE ou DISABLED.
 */
final class UserManager
{
    /**
     * @throws \InvalidArgumentException si une règle n'est pas respectée
     */
    public function validate(User $user): bool
    {
        $email = $user->getEmail();
        if ($email === null || trim($email) === '') {
            throw new \InvalidArgumentException('L\'email est obligatoire.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L\'email doit être valide.');
        }

        $statut = $user->getStatus();
        if (!in_array($statut, [User::STATUS_ACTIVE, User::STATUS_DISABLED], true)) {
            throw new \InvalidArgumentException('Le statut doit être ACTIVE ou DISABLED.');
        }

        return true;
    }
}
