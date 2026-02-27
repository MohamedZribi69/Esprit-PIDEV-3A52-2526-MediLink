<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaService
{
    private HttpClientInterface $httpClient;
    private string $secretKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->secretKey = (string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? $_SERVER['RECAPTCHA_SECRET_KEY'] ?? getenv('RECAPTCHA_SECRET_KEY') ?: '');
    }

    public function validate(string $token, ?string $ip = null): bool
    {
        if ($this->secretKey === '' || $token === '') {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => array_filter([
                    'secret' => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]),
                'timeout' => 10,
            ]);

            $data = $response->toArray(false);

            return isset($data['success']) && $data['success'] === true;
        } catch (\Throwable) {
            return false;
        }
    }
}

