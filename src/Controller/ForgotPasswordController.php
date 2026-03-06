<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailService $mailService
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email', ''));

            if ($email !== '') {
                /** @var User|null $user */
                $user = $em->getRepository(User::class)->findOneBy(['email' => mb_strtolower($email)]);

                if ($user instanceof User) {
                    $newPlainPassword = bin2hex(random_bytes(4)); // 8 caractères hex
                    $hashed = $hasher->hashPassword($user, $newPlainPassword);
                    $user->setPassword($hashed);
                    $em->flush();

                    $toName = $user->getFullName() !== null && $user->getFullName() !== ''
                        ? (string) $user->getFullName()
                        : (string) $user->getEmail();

                    $mailService->sendPasswordReset((string) $user->getEmail(), $toName, $newPlainPassword);
                }
            }

            $this->addFlash('success', 'Si un compte existe pour cet email, un nouveau mot de passe vous a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }
}

