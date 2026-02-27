<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailService $mailService
    ): Response {
        $lastEmail = '';

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email', ''));
            $lastEmail = $email;

            if ($email !== '') {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($user instanceof User) {
                    $newPlainPassword = bin2hex(random_bytes(4));
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPlainPassword);

                    $user->setPassword($hashedPassword);
                    $entityManager->flush();

                    $toName = method_exists($user, 'getFullName')
                        ? (string) $user->getFullName()
                        : (string) $user->getUserIdentifier();

                    $mailService->sendPasswordReset($email, $toName, $newPlainPassword);

                    $this->addFlash('success', 'Un nouveau mot de passe vous a été envoyé par e-mail.');

                    return $this->redirectToRoute('app_login');
                }

                $this->addFlash('error', 'Aucun compte trouvé pour cet email.');
            } else {
                $this->addFlash('error', 'Veuillez saisir une adresse e-mail.');
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'last_email' => $lastEmail,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
