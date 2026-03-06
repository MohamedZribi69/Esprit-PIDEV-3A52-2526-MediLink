<?php

namespace App\DTO;

/**
 * DTO pour les stats de dons par catégorie (liste admin).
 */
final class CategoryDonCountDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly int $total,
    ) {
    }
}
