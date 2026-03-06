<?php

namespace App\DTO;

/**
 * DTO pour le nombre de RDV par médecin (agrégation).
 */
final class MedecinRdvCountDTO
{
    public function __construct(
        public readonly int $medecinId,
        public readonly int $rdvCount,
    ) {
    }
}
