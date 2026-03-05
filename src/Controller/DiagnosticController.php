<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiagnosticController extends AbstractController
{
    #[Route('/admin/diagnostic/email', name: 'admin_diagnostic_email')]
    public function emailDiagnostic(): Response
    {
        $mailerDsn = $_ENV['MAILER_DSN'] ?? 'Non configuré';
        $notificationEmail = $_ENV['NOTIFICATION_EMAIL'] ?? 'Non configuré';
        
        // Masquer les données sensibles
        $displayDsn = preg_replace('/:[^@]*@/', ':****@', $mailerDsn);
        
        return $this->render('admin/diagnostic/email.html.twig', [
            'mailerDsn' => $displayDsn,
            'notificationEmail' => $notificationEmail,
        ]);
    }
}
