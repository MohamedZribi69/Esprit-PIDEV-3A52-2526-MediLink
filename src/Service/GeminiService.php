<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
    }



    public function genererDescriptionMedicament(string $medicament): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey;
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Rédigez un résumé court (2-3 phrases maximum) du médicament $medicament en tant que médecin. Mentionnez uniquement l'usage principal et la forme."]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($data)
            ]);
            
            $result = $response->toArray();
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $description = trim($result['candidates'][0]['content']['parts'][0]['text']);
                return ['success' => true, 'description' => $description, 'error' => null];
            }
            
            return ['success' => false, 'description' => null, 'error' => 'Format de réponse invalide'];
        } catch (\Exception $e) {
            return ['success' => false, 'description' => null, 'error' => $e->getMessage()];
        }
    }
}
