<?php

namespace App\Service;

/**
 * Génère une description d'événement à partir du titre, sans appel externe.
 */
final class EventDescriptionGenerator
{
    public function generateFromTitle(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        // Nettoyer et tronquer le titre pour l'intégrer proprement dans le texte
        $safeTitle = mb_substr($title, 0, 120);

        $types = [
            "atelier interactif",
            "conférence",
            "session d'information",
            "rencontre santé",
            "atelier de sensibilisation",
        ];

        $objectifs = [
            "mieux comprendre vos enjeux de santé au quotidien",
            "répondre à vos questions avec une équipe médicale dédiée",
            "vous donner des conseils concrets et faciles à appliquer",
            "échanger directement avec des professionnels de santé",
            "vous accompagner dans la prévention et le suivi de votre santé",
        ];

        $publics = [
            "patients et leurs proches",
            "toute personne intéressée par ce sujet",
            "les patients suivis par nos médecins ainsi que le grand public",
            "les patients de MediLink et toute personne concernée",
            "toutes les personnes souhaitant améliorer leur bien‑être",
        ];

        // Choix "pseudo‑aléatoire" mais déterministe basé sur le titre
        $hash = crc32($safeTitle);
        $type = $types[$hash % \count($types)];
        $objectif = $objectifs[$hash % \count($objectifs)];
        $public = $publics[$hash % \count($publics)];

        return sprintf(
            "« %s » est un %s organisé par MediLink pour %s. Cet événement est ouvert à %s et se déroulera dans une ambiance conviviale et professionnelle.",
            $safeTitle,
            $type,
            $objectif,
            $public
        );
    }
}
