<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class FaceVerificationController extends AbstractController
{
    #[Route('/face-verify', name: 'app_face_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '[]', true);
        $email = is_array($data) && isset($data['email']) ? trim((string) $data['email']) : '';

        if ($email === '') {
            return $this->json(['match' => false, 'error' => 'Email manquant'], 400);
        }

        // Stub très simple : on accepte toujours pour l'instant.
        return $this->json(['match' => true]);
    }
}

