<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceRecognitionService
{
    private HttpClientInterface $httpClient;
    private string $endpoint;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->endpoint = (string) ($_ENV['FACE_API_ENDPOINT'] ?? $_SERVER['FACE_API_ENDPOINT'] ?? getenv('FACE_API_ENDPOINT') ?: '');
        $this->apiKey = (string) ($_ENV['FACE_API_KEY'] ?? $_SERVER['FACE_API_KEY'] ?? getenv('FACE_API_KEY') ?: '');
    }

    /**
     * Vérifie le visage via une API externe.
     *
     * Cette méthode attend que l'API réponde au minimum avec un JSON contenant
     * une clé booléenne "match".
     */
    public function verify(string $email, string $imageBase64): bool
    {
        if ($this->endpoint === '' || $this->apiKey === '') {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', $this->endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $email,
                    'image' => $imageBase64,
                ],
                'timeout' => 15,
            ]);

            $data = $response->toArray(false);

            return isset($data['match']) && (bool) $data['match'] === true;
        } catch (\Throwable) {
            return false;
        }
    }
}

