<?php

namespace App\Service;

class MailService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->host = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
        $this->port = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'MediLink';
    }

    /**
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    private function createMailer()
    {
        require_once __DIR__ . '/../../mail/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../../mail/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../mail/PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->host;
        $mail->SMTPAuth = true;
        $mail->Username = $this->username;
        $mail->Password = $this->password;
        $mail->SMTPSecure = $this->encryption;
        $mail->Port = $this->port;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($this->fromEmail, $this->fromName);

        return $mail;
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $newPlainPassword): bool
    {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Réinitialisation de votre mot de passe MediLink';
            $mail->isHTML(true);

            $bodyHtml = '
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réinitialisation de votre mot de passe MediLink</title>
  <style>
    body { font-family: Arial, sans-serif; background-color:#f5f7fb; margin:0; padding:0; }
    .wrapper { width:100%; padding:20px 0; }
    .container { max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 3px 12px rgba(15,35,52,0.12); }
    .header { background:linear-gradient(135deg,#1a73e8,#0d8a72); padding:18px 24px; color:#ffffff; display:flex; align-items:center; }
    .logo-circle { width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; margin-right:10px; font-size:18px; }
    .brand { font-weight:700; font-size:18px; letter-spacing:.5px; }
    .brand span { color:#bfffea; }
    .content { padding:24px; color:#1f2933; }
    .content h1 { font-size:20px; margin:0 0 12px; color:#1a73e8; }
    .content p { font-size:14px; line-height:1.6; margin:0 0 10px; }
    .password-box { margin:18px 0; padding:14px 16px; border-radius:6px; background:#f1f5ff; border:1px solid #d0ddff; font-family:Consolas, monospace; font-size:16px; font-weight:600; color:#0b3b8c; letter-spacing:.5px; }
    .note { font-size:12px; color:#6b7280; margin-top:10px; }
    .footer { padding:14px 24px 18px; font-size:11px; color:#9ca3af; text-align:center; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="header">
        <div class="logo-circle">M</div>
        <div class="brand">Medi<span>Link</span></div>
      </div>
      <div class="content">
        <h1>Votre mot de passe a été réinitialisé</h1>
        <p>Bonjour ' . htmlspecialchars($toName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>
        <p>Un nouveau mot de passe a été généré pour votre compte <strong>MediLink</strong>.</p>
        <p>Voici votre nouveau mot de passe :</p>
        <div class="password-box">' . htmlspecialchars($newPlainPassword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>
        <p>Nous vous recommandons de vous connecter dès que possible puis de modifier ce mot de passe depuis votre espace utilisateur.</p>
        <p class="note">Si vous n\'êtes pas à l\'origine de cette demande, contactez rapidement l\'administrateur de la plateforme.</p>
      </div>
      <div class="footer">
        Cet email a été envoyé automatiquement par la plateforme MediLink. Merci de ne pas y répondre.
      </div>
    </div>
  </div>
</body>
</html>';

            $bodyText = "Bonjour {$toName},\n\n"
                . "Un nouveau mot de passe a été généré pour votre compte MediLink.\n\n"
                . "Nouveau mot de passe : {$newPlainPassword}\n\n"
                . "Nous vous recommandons de vous connecter dès que possible puis de modifier ce mot de passe depuis votre espace utilisateur.\n\n"
                . "Si vous n'êtes pas à l'origine de cette demande, contactez rapidement l'administrateur de la plateforme.\n\n"
                . "Ceci est un message automatique, merci de ne pas y répondre.";

            $mail->Body = $bodyHtml;
            $mail->AltBody = $bodyText;

            return $mail->send();
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendRegistrationCode(string $toEmail, string $toName, string $code): bool
    {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Code de vérification de votre compte MediLink';
            $mail->isHTML(true);

            $bodyHtml = '
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Code de vérification MediLink</title>
  <style>
    body { font-family: Arial, sans-serif; background-color:#f5f7fb; margin:0; padding:0; }
    .wrapper { width:100%; padding:20px 0; }
    .container { max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 3px 12px rgba(15,35,52,0.12); }
    .header { background:linear-gradient(135deg,#1a73e8,#0d8a72); padding:18px 24px; color:#ffffff; display:flex; align-items:center; }
    .logo-circle { width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; margin-right:10px; font-size:18px; }
    .brand { font-weight:700; font-size:18px; letter-spacing:.5px; }
    .brand span { color:#bfffea; }
    .content { padding:24px; color:#1f2933; }
    .content h1 { font-size:20px; margin:0 0 12px; color:#1a73e8; }
    .content p { font-size:14px; line-height:1.6; margin:0 0 10px; }
    .code-box { margin:18px 0; padding:14px 16px; border-radius:6px; background:#f1f5ff; border:1px solid #d0ddff; font-family:Consolas, monospace; font-size:18px; font-weight:700; color:#0b3b8c; letter-spacing:3px; text-align:center; }
    .note { font-size:12px; color:#6b7280; margin-top:10px; }
    .footer { padding:14px 24px 18px; font-size:11px; color:#9ca3af; text-align:center; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <div class="header">
        <div class="logo-circle">M</div>
        <div class="brand">Medi<span>Link</span></div>
      </div>
      <div class="content">
        <h1>Bienvenue sur MediLink</h1>
        <p>Bonjour ' . htmlspecialchars($toName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>
        <p>Merci d\'avoir créé un compte sur <strong>MediLink</strong>.</p>
        <p>Voici votre code de vérification&nbsp;:</p>
        <div class="code-box">' . htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>
        <p>Saisissez ce code dans l\'application pour finaliser l\'activation de votre compte.</p>
        <p class="note">Si vous n\'êtes pas à l\'origine de cette inscription, vous pouvez ignorer ce message.</p>
      </div>
      <div class="footer">
        Cet email a été envoyé automatiquement par la plateforme MediLink. Merci de ne pas y répondre.
      </div>
    </div>
  </div>
</body>
</html>';

            $bodyText = "Bonjour {$toName},\n\n"
                . "Merci d'avoir créé un compte sur MediLink.\n\n"
                . "Voici votre code de vérification : {$code}\n\n"
                . "Saisissez ce code dans l'application pour finaliser l'activation de votre compte.\n\n"
                . "Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer ce message.\n\n"
                . "Ceci est un message automatique, merci de ne pas y répondre.";

            $mail->Body = $bodyHtml;
            $mail->AltBody = $bodyText;

            return $mail->send();
        } catch (\Throwable) {
            return false;
        }
    }
}

