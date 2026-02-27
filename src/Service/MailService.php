<?php

namespace App\Service;

require_once __DIR__ . '/../../mail/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../mail/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../mail/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->host = (string) ($_ENV['MAIL_HOST'] ?? $_SERVER['MAIL_HOST'] ?? getenv('MAIL_HOST') ?: '');
        $this->port = (int) ($_ENV['MAIL_PORT'] ?? $_SERVER['MAIL_PORT'] ?? getenv('MAIL_PORT') ?: 587);
        $this->encryption = (string) ($_ENV['MAIL_ENCRYPTION'] ?? $_SERVER['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?: 'tls');
        $this->username = (string) ($_ENV['MAIL_USERNAME'] ?? $_SERVER['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?: '');
        $this->password = (string) ($_ENV['MAIL_PASSWORD'] ?? $_SERVER['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?: '');
        $this->fromAddress = (string) ($_ENV['MAIL_FROM_ADDRESS'] ?? $_SERVER['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: '');
        $this->fromName = (string) ($_ENV['MAIL_FROM_NAME'] ?? $_SERVER['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'MediLink');
    }

    private function createConfiguredMailer(): ?PHPMailer
    {
        if ($this->host === '' || $this->username === '' || $this->password === '' || $this->fromAddress === '') {
            return null;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->host;
        $mail->SMTPAuth = true;
        $mail->Username = $this->username;
        $mail->Password = $this->password;
        $mail->SMTPSecure = $this->encryption ?: PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $this->port ?: 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($this->fromAddress, $this->fromName);

        return $mail;
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $newPlainPassword): bool
    {
        try {
            $mail = $this->createConfiguredMailer();
            if (!$mail instanceof PHPMailer) {
                return false;
            }

            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe MediLink';

            $htmlBody = '
            <div style="font-family: Arial, sans-serif; background-color:#f4f7fb; padding:24px;">
              <div style="max-width:520px; margin:0 auto; background-color:#ffffff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.08); overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1a73e8,#0d8a72); padding:20px 24px; color:#ffffff; display:flex; align-items:center;">
                  <div style="width:40px; height:40px; border-radius:50%; background-color:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:bold; margin-right:12px;">
                    M
                  </div>
                  <div>
                    <div style="font-size:18px; font-weight:600;">MediLink</div>
                    <div style="font-size:13px; opacity:0.9;">Plateforme de gestion médicale</div>
                  </div>
                </div>

                <div style="padding:24px;">
                  <h1 style="font-size:20px; margin:0 0 12px; color:#202124;">Votre mot de passe a été réinitialisé</h1>
                  <p style="margin:0 0 12px; font-size:14px; color:#5f6368;">
                    Bonjour ' . htmlspecialchars($toName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',<br><br>
                    Vous avez demandé la réinitialisation de votre mot de passe sur MediLink. Voici votre nouveau mot de passe temporaire :
                  </p>

                  <div style="margin:18px 0; padding:14px 16px; background-color:#e8f0fe; border-radius:10px; text-align:center; font-family:\'SFMono-Regular\',Menlo,Monaco,Consolas,\'Liberation Mono\',\'Courier New\',monospace; font-weight:bold; letter-spacing:0.08em; color:#1a73e8; font-size:16px;">
                    ' . htmlspecialchars($newPlainPassword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '
                  </div>

                  <p style="margin:0 0 12px; font-size:14px; color:#5f6368;">
                    Utilisez ce mot de passe pour vous connecter, puis modifiez-le depuis votre espace personnel pour plus de sécurité.
                  </p>

                  <p style="margin:16px 0 0; font-size:12px; color:#9aa0a6;">
                    Cet e-mail a été envoyé automatiquement, merci de ne pas y répondre.
                  </p>
                </div>
              </div>
            </div>';

            $altBody = sprintf(
                "Bonjour %s,\n\nVous avez demandé la réinitialisation de votre mot de passe sur MediLink.\n\nVotre nouveau mot de passe temporaire est : %s\n\nUtilisez ce mot de passe pour vous connecter, puis modifiez-le depuis votre espace personnel.\n\nCet e-mail a été envoyé automatiquement, merci de ne pas y répondre.",
                $toName,
                $newPlainPassword
            );

            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody;

            return $mail->send();
        } catch (Exception) {
            return false;
        }
    }

    public function sendRegistrationCode(string $toEmail, string $toName, string $code): bool
    {
        try {
            $mail = $this->createConfiguredMailer();
            if (!$mail instanceof PHPMailer) {
                return false;
            }

            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = 'Code de vérification de votre compte MediLink';

            $htmlBody = '
            <div style="font-family: Arial, sans-serif; background-color:#f4f7fb; padding:24px;">
              <div style="max-width:520px; margin:0 auto; background-color:#ffffff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.08); overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1a73e8,#0d8a72); padding:20px 24px; color:#ffffff; display:flex; align-items:center;">
                  <div style="width:40px; height:40px; border-radius:50%; background-color:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:bold; margin-right:12px;">
                    M
                  </div>
                  <div>
                    <div style="font-size:18px; font-weight:600;">MediLink</div>
                    <div style="font-size:13px; opacity:0.9;">Plateforme de gestion médicale</div>
                  </div>
                </div>

                <div style="padding:24px;">
                  <h1 style="font-size:20px; margin:0 0 12px; color:#202124;">Bienvenue sur MediLink</h1>
                  <p style="margin:0 0 12px; font-size:14px; color:#5f6368;">
                    Bonjour ' . htmlspecialchars($toName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',<br><br>
                    Merci d\'avoir créé un compte sur MediLink. Pour finaliser votre inscription, veuillez entrer le code de vérification ci-dessous dans l\'application :
                  </p>

                  <div style="margin:18px 0; padding:16px 18px; background-color:#e6f4ea; border-radius:12px; text-align:center;">
                    <div style="font-family:\'SFMono-Regular\',Menlo,Monaco,Consolas,\'Liberation Mono\',\'Courier New\',monospace; font-weight:bold; letter-spacing:0.25em; font-size:20px; color:#0d8a72;">
                      ' . htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '
                    </div>
                  </div>

                  <p style="margin:0 0 12px; font-size:14px; color:#5f6368;">
                    Si vous n\'êtes pas à l\'origine de cette inscription, vous pouvez ignorer cet e-mail en toute sécurité.
                  </p>

                  <p style="margin:16px 0 0; font-size:12px; color:#9aa0a6;">
                    Cet e-mail a été envoyé automatiquement, merci de ne pas y répondre.
                  </p>
                </div>
              </div>
            </div>';

            $altBody = sprintf(
                "Bonjour %s,\n\nMerci d'avoir créé un compte sur MediLink.\n\nVotre code de vérification est : %s\n\nVeuillez entrer ce code dans l'application pour finaliser votre inscription.\n\nSi vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet e-mail.\n\nCet e-mail a été envoyé automatiquement, merci de ne pas y répondre.",
                $toName,
                $code
            );

            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody;

            return $mail->send();
        } catch (Exception) {
            return false;
        }
    }

    public function sendAccountVerification(string $toEmail, string $toName, string $verificationUrl): bool
    {
        try {
            $mail = $this->createConfiguredMailer();
            if (!$mail instanceof PHPMailer) {
                return false;
            }

            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = 'Vérification de votre compte MediLink';

            $safeName = htmlspecialchars($toName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $htmlBody = '
            <div style="font-family: Arial, sans-serif; background-color:#f4f7fb; padding:24px;">
              <div style="max-width:520px; margin:0 auto; background-color:#ffffff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.08); overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1a73e8,#0d8a72); padding:20px 24px; color:#ffffff; display:flex; align-items:center;">
                  <div style="width:40px; height:40px; border-radius:50%; background-color:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:bold; margin-right:12px;">
                    M
                  </div>
                  <div>
                    <div style="font-size:18px; font-weight:600;">MediLink</div>
                    <div style="font-size:13px; opacity:0.9;">Plateforme de gestion médicale</div>
                  </div>
                </div>

                <div style="padding:24px;">
                  <h1 style="font-size:20px; margin:0 0 12px; color:#202124;">Confirmez votre adresse e-mail</h1>
                  <p style="margin:0 0 12px; font-size:14px; color:#5f6368;">
                    Bonjour ' . $safeName . ',<br><br>
                    Merci d\'avoir créé un compte sur MediLink. Pour activer votre compte, veuillez cliquer sur le bouton ci-dessous :
                  </p>

                  <div style="margin:20px 0; text-align:center;">
                    <a href="' . $safeUrl . '" style="display:inline-block; padding:12px 22px; background-color:#0d8a72; color:#ffffff; text-decoration:none; border-radius:999px; font-weight:600; font-size:14px;">
                      Vérifier mon compte
                    </a>
                  </div>

                  <p style="margin:0 0 12px; font-size:13px; color:#5f6368;">
                    Si le bouton ne fonctionne pas, vous pouvez copier/coller ce lien dans votre navigateur :
                  </p>
                  <p style="margin:0 0 12px; font-size:12px; color:#1a73e8; word-break:break-all;">
                    ' . $safeUrl . '
                  </p>

                  <p style="margin:16px 0 0; font-size:12px; color:#9aa0a6;">
                    Si vous n\'êtes pas à l\'origine de cette inscription, vous pouvez ignorer cet e-mail en toute sécurité.<br>
                    Cet e-mail a été envoyé automatiquement, merci de ne pas y répondre.
                  </p>
                </div>
              </div>
            </div>';

            $altBody = sprintf(
                "Bonjour %s,\n\nMerci d'avoir créé un compte sur MediLink.\n\nPour activer votre compte, veuillez ouvrir ce lien dans votre navigateur :\n%s\n\nSi vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet e-mail.\n\nCet e-mail a été envoyé automatiquement, merci de ne pas y répondre.",
                $toName,
                $verificationUrl
            );

            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody;

            return $mail->send();
        } catch (Exception) {
            return false;
        }
    }
}

