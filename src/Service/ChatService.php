<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $model;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = (string) ($_ENV['AI_API_KEY'] ?? $_SERVER['AI_API_KEY'] ?? getenv('AI_API_KEY') ?: '');
        $this->model = (string) ($_ENV['AI_MODEL'] ?? $_SERVER['AI_MODEL'] ?? getenv('AI_MODEL') ?: 'gpt-4.1-mini');
    }

    public function getMedicalReply(string $userMessage): string
    {
        $trimmed = trim($userMessage);

        // Garde simple côté serveur : si le message est clairement hors sujet, on force une réponse fixe.
        $medicalKeywords = ['santé', 'sante', 'médecin', 'medecin', 'malade', 'symptôme', 'symptome', 'douleur', 'rendez-vous', 'rendez vous', 'ordonnance', 'médicament', 'medicament', 'analyse', 'examen', 'consultation', 'hopital', 'hôpital', 'clinique'];
        $isProbablyMedical = false;
        foreach ($medicalKeywords as $keyword) {
            if (stripos($trimmed, $keyword) !== false) {
                $isProbablyMedical = true;
                break;
            }
        }

        if (!$isProbablyMedical) {
            return "Je suis l'assistant médical de MediLink. Je peux vous aider pour des questions de santé, de rendez-vous médicaux ou d'utilisation de la plateforme MediLink. Posez-moi une question dans ce domaine.";
        }

        if ($this->apiKey === '' || $this->model === '') {
            return "Le service d'assistance médicale MediLink n'est pas encore configuré. Merci de réessayer plus tard.";
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are the MediLink Medical Assistant. You ONLY answer questions related to medicine, health, medical appointments, or how to use the MediLink platform. If the user asks about anything else (politics, games, general chit-chat, etc.), you MUST refuse and say you can only answer medical or MediLink-related questions, and gently redirect them back to a health-related topic. Always answer in French, in a short and clear way. Do not mention that you are an AI model.",
                        ],
                        [
                            'role' => 'user',
                            'content' => $trimmed,
                        ],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 400,
                ],
                'timeout' => 15,
            ]);

            $data = $response->toArray(false);

            if (!isset($data['choices'][0]['message']['content'])) {
                return "Je suis l'assistant médical de MediLink. Je peux vous aider pour des questions de santé, de rendez-vous ou d'utilisation de la plateforme MediLink.";
            }

            return (string) $data['choices'][0]['message']['content'];
        } catch (\Throwable) {
            return "Je suis l'assistant médical de MediLink. Une erreur technique est survenue, merci de réessayer dans quelques instants.";
        }
    }
}

