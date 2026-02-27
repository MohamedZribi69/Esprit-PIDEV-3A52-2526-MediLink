<?php

namespace App\Controller;

use App\Service\ChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/assistant', name: 'app_assistant', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('assistant/chat.html.twig');
    }

    #[Route('/assistant/message', name: 'app_assistant_message', methods: ['POST'])]
    public function message(Request $request, ChatService $chatService): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true) ?? [];
        $message = isset($data['message']) ? (string) $data['message'] : '';

        if (trim($message) === '') {
            return $this->json([
                'success' => false,
                'reply' => "Je suis l'assistant médical de MediLink. Posez-moi une question liée à la santé, à vos rendez-vous ou à l'utilisation de la plateforme MediLink.",
            ]);
        }

        $reply = $chatService->getMedicalReply($message);

        return $this->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }
}

