<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\CaptchaService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailService $mailService,
        CaptchaService $captchaService
    ): Response {
        $user = new User();

        // valeurs par défaut
        if (method_exists($user, 'setRoles')) {
            $user->setRoles(['ROLE_USER']);
        }

        if (method_exists($user, 'setStatus') && defined(User::class.'::STATUS_DISABLED')) {
            $user->setStatus(User::STATUS_DISABLED);
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $captchaValid = true;
        if ($form->isSubmitted()) {
            $captchaToken = (string) $request->request->get('g-recaptcha-response', '');
            if ($captchaToken === '' || !$captchaService->validate($captchaToken, $request->getClientIp())) {
                $captchaValid = false;
                $this->addFlash('error', 'Veuillez confirmer que vous n\'êtes pas un robot.');
            }
        }

        if ($form->isSubmitted() && $form->isValid() && $captchaValid) {
            $hashedPassword = $hasher->hashPassword(
                $user,
                (string) $form->get('plainPassword')->getData()
            );

            $user->setPassword($hashedPassword);

            // Génération du token de vérification
            if (method_exists($user, 'setVerificationToken')) {
                $user->setVerificationToken(bin2hex(random_bytes(32)));
            }

            $em->persist($user);
            $em->flush();

            $toEmail = method_exists($user, 'getEmail') ? (string) $user->getEmail() : (string) $user->getUserIdentifier();
            $toName = method_exists($user, 'getFullName')
                ? (string) $user->getFullName()
                : (string) $user->getUserIdentifier();

            $verificationToken = method_exists($user, 'getVerificationToken')
                ? (string) $user->getVerificationToken()
                : '';

            if ($verificationToken !== '') {
                $verificationUrl = $this->generateUrl(
                    'app_verify_account',
                    ['token' => $verificationToken],
                    \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
                );

                $mailService->sendAccountVerification($toEmail, $toName, $verificationUrl);
            }

            $this->addFlash('success', "Votre compte a été créé. Merci de vérifier votre adresse e-mail pour l'activer.");

            return $this->redirectToRoute('app_login');
        }

        $recaptchaSiteKey = (string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? $_SERVER['RECAPTCHA_SITE_KEY'] ?? getenv('RECAPTCHA_SITE_KEY') ?: '');

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => $recaptchaSiteKey,
        ]);
    }

    #[Route('/verify-account/{token}', name: 'app_verify_account', methods: ['GET'])]
    public function verifyAccount(string $token, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user instanceof User) {
            $this->addFlash('error', 'Lien de vérification invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        if (method_exists($user, 'setStatus')) {
            $user->setStatus(User::STATUS_ACTIVE);
        }
        if (method_exists($user, 'setVerificationToken')) {
            $user->setVerificationToken(null);
        }

        $em->flush();

        $this->addFlash('success', 'Votre compte a été vérifié et activé. Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
