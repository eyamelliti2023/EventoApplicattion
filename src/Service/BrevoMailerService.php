<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoMailerService
{
    private string $apiKey = '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';
    private string $senderEmail = 'gmira9504@gmail.com';
    private string $senderName = 'eventify';
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendOtpEmail(string $toEmail, string $otp): void
    {
        $response = $this->client->request('POST', 'https://api.brevo.com/v3/smtp/email', [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'sender' => [
                    'name' => $this->senderName,
                    'email' => $this->senderEmail,
                ],
                'to' => [
                    ['email' => $toEmail]
                ],
                'subject' => 'Votre code OTP pour réinitialisation',
                'htmlContent' => "
                    <html>
                        <body>
                            <h1>Votre Code OTP</h1>
                            <p>Voici votre code de vérification : <strong style='font-size:24px;'>$otp</strong></p>
                            <p>Ce code est valide pendant 10 minutes.</p>
                        </body>
                    </html>
                "
            ],
        ]);
    
        if (!in_array($response->getStatusCode(), [200, 201])) {
            throw new \Exception('Erreur lors de l\'envoi du mail Brevo : ' . $response->getContent(false));
        }
    
        
    }
    
}
