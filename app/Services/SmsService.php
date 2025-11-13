<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS confirmation code
     *
     * @param string $phoneNumber
     * @param string $name
     * @param string $code
     * @return bool
     */
    public function sendConfirmationCode(string $phoneNumber, string $name, string $code): bool
    {
        return $this->sendWithTwilio($phoneNumber, $name, $code);
    }

    /**
     * Send SMS using Twilio
     */
    private function sendWithTwilio(string $phoneNumber, string $name, string $code): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::error('Twilio configuration manquante');
            return false;
        }

        try {
            $client = new \Twilio\Rest\Client($sid, $token);
            $client->messages->create(
                $phoneNumber,
                [
                    'from' => $from,
                    'body' => "Bonjour $name, Votre code de confirmation est : $code. Merci."
                ]
            );
            Log::info('SMS envoyé avec succès via Twilio', [
                'destinataire' => $phoneNumber,
                'nom' => $name,
                'code' => $code
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur Twilio SMS', ['error' => $e->getMessage()]);
            return false;
        }
    }
}