<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendOrdonnanceNotification(
        string $medicineDoctor,
        string $patientName,
        string $medicaments,
        string $recipientEmail
    ): bool {
        try {
            $gmailEmail = $_ENV['GMAIL_EMAIL'] ?? 'saifeddinegrabi@gmail.com';
            $htmlContent = $this->getOrdonnanceEmailHtml($medicineDoctor, $patientName, $medicaments);

            $email = (new Email())
                ->from($gmailEmail)
                ->to($recipientEmail)
                ->subject('Nouvelle Ordonnance Créée - MediLink')
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Email Error: ' . $e->getMessage());
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
