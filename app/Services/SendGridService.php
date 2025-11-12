<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    /**
     * Send a simple confirmation email via SendGrid API
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $code
     * @return bool
     */
    public function sendConfirmation(string $toEmail, string $toName, string $code): bool
    {
        $apiKey = config('services.sendgrid.key') ?: env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        $fromEmail = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS');
        $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME');

        $payload = [
            'personalizations' => [
                [
                    'to' => [
                        ['email' => $toEmail, 'name' => $toName],
                    ],
                    'subject' => 'Confirmation de votre compte',
                ],
            ],
            'from' => ['email' => $fromEmail, 'name' => $fromName],
            'content' => [
                ['type' => 'text/plain', 'value' => "Bonjour $toName,\n\nVotre code de confirmation est : $code\n\nMerci."]
            ],
        ];

        $response = Http::withToken($apiKey)
            ->post('https://api.sendgrid.com/v3/mail/send', $payload);

        if ($response->status() === 202) {
            return true;
        } else {
            // Log the error for debugging
            Log::error('SendGrid API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
            return false;
        }
    }
}
