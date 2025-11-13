<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    /**
     * Send a simple confirmation SMS
     * Note: SendGrid doesn't have native SMS API, using simulation for now
     *
     * @param string $toPhone
     * @param string $toName
     * @param string $code
     * @return bool
     */
    public function sendConfirmation(string $toPhone, string $toName, string $code): bool
    {
        // For now, simulate SMS sending - log the SMS content
        Log::info('SMS envoyé (simulation)', [
            'to' => $toPhone,
            'from' => 'VotreApp',
            'content' => "Bonjour $toName, Votre code de confirmation est : $code. Merci.",
            'code' => $code
        ]);

        // In production, you would integrate with a real SMS service like:
        // - Twilio: https://www.twilio.com/docs/sms/api
        // - AWS SNS: https://docs.aws.amazon.com/sns/latest/dg/sms_publish-to-phone.html
        // - Orange SMS API, etc.

        // For testing purposes, always return true
        return true;
    }
}
