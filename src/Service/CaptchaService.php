<?php

namespace App\Service;

final class CaptchaService
{
    /**
     * Valide toujours le captcha pour l'instant.
     * Tu pourras plus tard brancher ici un vrai service (reCAPTCHA, hCaptcha, etc.).
     */
    public function validate(string $token, ?string $ip = null): bool
    {
        if (trim($token) === '') {
            return false;
        }

        return true;
    }
}

