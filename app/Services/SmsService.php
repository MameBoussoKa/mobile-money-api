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
        // Simulation pour les tests - en production, remplacez par un vrai service SMS
        Log::info('SMS de confirmation envoyé (simulation)', [
            'destinataire' => $phoneNumber,
            'nom' => $name,
            'code' => $code,
            'message' => "Bonjour $name, Votre code de confirmation est : $code. Merci."
        ]);

        // Pour un vrai service SMS, vous pouvez utiliser :
        // - Twilio: https://www.twilio.com/docs/sms/api
        // - AWS SNS: https://docs.aws.amazon.com/sns/latest/dg/sms_publish-to-phone.html
        // - OVH SMS: https://docs.ovh.com/fr/sms/
        // - Orange SMS API, etc.

        return true;
    }

    /**
     * Example implementation with Twilio (uncomment and configure if needed)
     */
    /*
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
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur Twilio SMS', ['error' => $e->getMessage()]);
            return false;
        }
    }
    */
}