<?php

namespace App\Controller;

use App\Service\FaceRecognitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FaceAuthController extends AbstractController
{
    #[Route('/face-verify', name: 'app_face_verify', methods: ['POST'])]
    public function verify(Request $request, FaceRecognitionService $faceRecognitionService): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true) ?? [];
        $email = isset($data['email']) ? (string) $data['email'] : '';
        $image = isset($data['image']) ? (string) $data['image'] : '';

        if (trim($email) === '' || trim($image) === '') {
            return $this->json([
                'success' => false,
                'match' => false,
                'message' => 'Email ou image manquant.',
            ]);
        }

        $match = $faceRecognitionService->verify($email, $image);

        return $this->json([
            'success' => true,
            'match' => $match,
        ]);
    }
}

