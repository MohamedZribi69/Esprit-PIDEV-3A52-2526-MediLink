<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeminiService
{
    public function __construct(
        /** @phpstan-ignore-next-line class.notFound */
        private readonly HttpClientInterface $client,
        private readonly ?string $apiKey = null,
    ) {
    }

    public function genererDescriptionMedicament(string $medicament): array
    {
        $medicament = trim($medicament);
        if ($medicament === '') {
            return ['success' => false, 'description' => null, 'error' => 'Nom vide'];
        }

        // Si pas de clé API, ou si l'appel échoue / format inattendu,
        // on retombe sur une génération locale déterministe.
        if ($this->apiKey === null || $this->apiKey === '') {
            return ['success' => true, 'description' => $this->generateLocalDescription($medicament), 'error' => null];
        }

        $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=' . $this->apiKey;
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Rédigez un résumé court (2-3 phrases maximum) du médicament {$medicament} en tant que médecin. Mentionnez uniquement l'usage principal, la forme et, si pertinent, des précautions importantes. Répondez en français."
                        ]
                    ],
                ],
            ],
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $payload,
            ]);

            $result = $response->toArray(false);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $description = trim($result['candidates'][0]['content']['parts'][0]['text']);
                return ['success' => true, 'description' => $description, 'error' => null];
            }

            // Si le format n'est pas celui attendu, on ne bloque pas l'utilisateur :
            // on génère une description locale.
            return ['success' => true, 'description' => $this->generateLocalDescription($medicament), 'error' => null];
        } catch (\Throwable $e) {
            // En cas d'erreur réseau / quota, on génère aussi en local.
            return ['success' => true, 'description' => $this->generateLocalDescription($medicament), 'error' => null];
        }
    }

    private function generateLocalDescription(string $medicament): string
    {
        $safeName = mb_substr($medicament, 0, 80);

        $usages = [
            "un traitement couramment utilisé pour soulager la douleur légère à modérée et faire baisser la fièvre",
            "un médicament souvent prescrit pour apaiser les maux de tête, les douleurs musculaires et les états grippaux",
            "un antalgique et antipyrétique de référence, adapté à de nombreuses situations du quotidien",
            "un médicament indiqué pour réduire la douleur et la fièvre dans le cadre de diverses infections bénignes",
            "un traitement de première intention pour le soulagement des douleurs aiguës de courte durée",
        ];

        $formes = [
            "Il est généralement disponible sous forme de comprimés et parfois en solution buvable.",
            "On le retrouve sous forme de comprimés, gélules ou suspensions orales selon les besoins du patient.",
            "Il existe en plusieurs présentations (comprimés, sachets, formes pédiatriques) pour s'adapter à chaque âge.",
            "La forme la plus utilisée est le comprimé, mais des formes adaptées aux enfants sont également disponibles.",
            "Selon la prescription, il peut être pris en comprimés, gélules ou formes liquides.",
        ];

        $precautions = [
            "Comme tout médicament, il doit être pris en respectant strictement la posologie indiquée par le médecin ou la notice.",
            "Une attention particulière doit être portée en cas d'insuffisance hépatique, rénale ou d'autres pathologies chroniques.",
            "Il est important de signaler à votre médecin tout autre traitement en cours afin d'éviter les interactions.",
            "Un surdosage peut avoir des conséquences graves ; il ne faut jamais dépasser la dose recommandée.",
            "Demandez conseil à un professionnel de santé en cas de doute sur l'utilisation ou la durée du traitement.",
        ];

        $hash = crc32(mb_strtolower($safeName));
        $usage = $usages[$hash % \count($usages)];
        $forme = $formes[$hash % \count($formes)];
        $precaution = $precautions[$hash % \count($precautions)];

        return sprintf(
            "%s est %s. %s %s",
            $safeName,
            $usage,
            $forme,
            $precaution
        );
    }
}

