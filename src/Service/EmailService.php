<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;
    private string $adminEmail;
    private string $fromEmail;
    private LoggerInterface $logger;

    public function __construct(
        MailerInterface $mailer,
        string $adminEmail,
        string $fromEmail,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->fromEmail = $fromEmail;
        $this->logger = $logger;
    }

    /**
     * Envoie un email à l'admin lors de l'ajout d'une ordonnance.
     */
    public function sendOrdonnanceNotification(
        string $medicineDoctor,
        string $patientName,
        string $medicaments
    ): bool {
        try {
            $htmlContent = $this->getOrdonnanceEmailHtml($medicineDoctor, $patientName, $medicaments);

            $email = (new Email())
                ->from(new Address($this->fromEmail, 'MediLink'))
                ->to($this->adminEmail)
                ->subject('Nouvelle Ordonnance Créée - MediLink')
                ->html($htmlContent);

            $this->mailer->send($email);
            $this->logger->info('Email ordonnance envoyé à l\'admin.', ['to' => $this->adminEmail]);
            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Erreur envoi email ordonnance: ' . $e->getMessage(), [
                'exception' => $e,
                'to' => $this->adminEmail,
            ]);
            return false;
        }
    }

    private function getOrdonnanceEmailHtml(string $doctor, string $patient, string $medicaments): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Ordonnance</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background-color: #2563eb; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; margin: -20px -20px 20px -20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; color: #2563eb; margin-bottom: 10px; font-size: 16px; border-bottom: 2px solid #2563eb; padding-bottom: 5px; }
        .info-row { display: flex; margin-bottom: 8px; }
        .label { width: 140px; font-weight: bold; color: #555; }
        .value { flex: 1; }
        .medicament-list { background-color: #f9fafb; padding: 15px; border-radius: 6px; border-left: 4px solid #2563eb; }
        .medicament-list ul { margin: 0; padding-left: 20px; }
        .medicament-list li { margin-bottom: 8px; }
        .footer { border-top: 1px solid #e5e7eb; padding-top: 15px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Nouvelle Ordonnance Créée</h1>
        </div>
        
        <div class="section">
            <div class="section-title">👨‍⚕️ Médecin</div>
            <div class="info-row">
                <span class="label">Nom :</span>
                <span class="value"><strong>$doctor</strong></span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">👤 Patient</div>
            <div class="info-row">
                <span class="label">Nom :</span>
                <span class="value"><strong>$patient</strong></span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">💊 Médicaments Prescrits</div>
            <div class="medicament-list">
                $medicaments
            </div>
        </div>

        <div class="footer">
            <p>✉️ Cet email a été envoyé automatiquement par MediLink</p>
            <p>© 2026 MediLink - Système de Gestion Médical. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
