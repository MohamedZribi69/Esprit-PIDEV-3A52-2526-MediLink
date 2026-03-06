<?php

namespace App\DTO;

/**
 * DTO pour le graphique par catégorie (dashboard).
 */
final class CategoryDonChartDTO
{
    public function __construct(
        public readonly string $nom,
        public readonly ?string $couleur,
        public readonly int $total,
    ) {
    }
}
