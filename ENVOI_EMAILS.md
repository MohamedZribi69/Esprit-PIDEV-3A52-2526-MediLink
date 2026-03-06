# Envoi d'e-mails – Récapitulatif des fonctionnalités

Toutes les fonctionnalités d'envoi d'e-mail du projet et la configuration à utiliser pour qu'elles fonctionnent.

---

## 1. Configuration utilisée

| Contexte | Variable(s) | Fichier |
|----------|-------------|---------|
| **Symfony Mailer** (majorité des envois) | `MAILER_DSN`, `MAILER_FROM` | `.env` |
| **PHPMailer** (inscription, mot de passe oublié) | `MAIL_HOST`, `MAIL_PORT`, `MAIL_ENCRYPTION`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | `.env` |

En cas d'erreur SSL avec Gmail, voir **MAILER_SSL_FIX.md** (pour `MAILER_DSN`, ajouter `?verify_peer=0` en dev).  
Pour **mot de passe oublié** et **création de compte** (PHPMailer), ajouter dans `.env` : **`MAIL_VERIFY_PEER=0`** (dev local uniquement).

---

## 2. Liste des flux d'envoi

| # | Fonctionnalité | Déclencheur | Service / endroit | Config |
|---|----------------|-------------|-------------------|--------|
| 1 | **Confirmation de rendez-vous** (prise de RDV) | Patient réserve un créneau | `RendezVousService::creerRendezVous()` → `RendezVousMailerService::envoyerConfirmationRendezVous()` | MAILER_DSN |
| 2 | **Acceptation de rendez-vous** (RDV accepté par le médecin ou l’admin) | Médecin clique « Confirmer » ou Admin clique « Confirmer » | `RendezVousService::confirmerRendezVous()` → `RendezVousMailerService::envoyerAcceptationRendezVous()` | MAILER_DSN |
| 3 | **Reçu de don par e-mail** | Admin clique « Envoyer le reçu par e-mail au patient » sur un don validé | `DonController::envoyerRecuEmail()` (MailerInterface) | MAILER_DSN, MAILER_FROM |
| 4 | **Notification ordonnance** (à l’admin) | Médecin enregistre une ordonnance | `OrdonnanceController` → `EmailService::sendOrdonnanceNotification()` | MAILER_DSN, MAILER_FROM, ADMIN_EMAIL |
| 5 | **Événement supprimé** (aux participants) | Admin supprime un événement | `EventAdminController::delete()` → `EventNotificationMailer::sendEventDeletedNotification()` | MAILER_DSN |
| 6 | **Inscription – vérification de compte** | Utilisateur s’inscrit | `RegistrationController` → `MailService::sendAccountVerification()` | MAIL_* (PHPMailer) |
| 7 | **Mot de passe oublié** | Utilisateur demande une réinitialisation | `ForgotPasswordController` → `MailService::sendPasswordReset()` | MAIL_* (PHPMailer) |
| 8 | **Code d’inscription** (si utilisé) | Envoi du code de vérification | `MailService::sendRegistrationCode()` | MAIL_* (PHPMailer) |

---

## 3. Vérifications rapides

- **Rendez-vous** : après création d’un RDV → email « Confirmation de votre rendez-vous ». Après confirmation (médecin ou admin) → email « Votre rendez-vous a été accepté ».
- **Dons** : don validé → action « Envoyer le reçu par e-mail au patient » → email avec reçu PDF.
- **Ordonnances** : création d’une ordonnance par un médecin → email à l’admin (adresse `ADMIN_EMAIL`).
- **Événements** : suppression d’un événement avec participants → email à chaque participant.
- **Inscription / mot de passe** : dépendent de `MAIL_*` (PHPMailer) ; en cas d’erreur SSL, configurer PHPMailer ou utiliser le même SMTP que `MAILER_DSN` si possible.

---

## 4. Fichiers principaux

- `src/Service/RendezVousMailerService.php` – emails RDV (confirmation + acceptation)
- `src/Service/RendezVousService.php` – appelle le mailer à la création et à la confirmation
- `src/Controller/Admin/DonController.php` – envoi reçu don
- `src/Service/EmailService.php` – notification ordonnance
- `src/Service/EventNotificationMailer.php` – événement supprimé
- `src/Service/MailService.php` – inscription et mot de passe (PHPMailer)
- `config/packages/mailer.yaml` – DSN Symfony Mailer
