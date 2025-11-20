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
        // For testing in local environment, mock SMS sending
        if (config('app.env') === 'local') {
            Log::info('SMS mocked for local testing', [
                'destinataire' => $this->formatPhoneNumber($phoneNumber),
                'nom' => $name,
                'code' => $code
            ]);
            return true;
        }

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::error('Twilio configuration manquante');
            return false;
        }

        // Format phone number with country code if missing
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        try {
            $client = new \Twilio\Rest\Client($sid, $token);
            $message = $client->messages->create(
                $formattedPhone,
                [
                    'from' => $from,
                    'body' => "Bonjour $name, Votre code de confirmation est : $code. Merci."
                ]
            );
            Log::info('SMS envoyé avec succès via Twilio', [
                'destinataire' => $formattedPhone,
                'nom' => $name,
                'code' => $code,
                'message_sid' => $message->sid,
                'status' => $message->status
            ]);
            return true;
        } catch (\Twilio\Exceptions\RestException $e) {
            Log::error('Erreur Twilio REST API', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'phone' => $formattedPhone
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Erreur générale Twilio SMS', [
                'error' => $e->getMessage(),
                'phone' => $formattedPhone
            ]);
            return false;
        }
    }

    /**
     * Format phone number with country code
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any existing + or spaces
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        // If it starts with country code, assume it's already formatted
        if (str_starts_with($phoneNumber, '221')) {
            return '+' . $phoneNumber;
        }

        // For Senegal (Dakar), add +221 if not present
        if (str_starts_with($phoneNumber, '7') || str_starts_with($phoneNumber, '3')) {
            return '+221' . $phoneNumber;
        }

        // If it doesn't start with +, add it
        if (!str_starts_with($phoneNumber, '+')) {
            return '+' . $phoneNumber;
        }

        return $phoneNumber;
    }
}