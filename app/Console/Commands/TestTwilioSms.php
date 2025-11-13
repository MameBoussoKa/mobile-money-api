<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Command;

class TestTwilioSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-twilio-sms {phone?} {name?} {code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Twilio SMS sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone') ?: '+221785942490';
        $name = $this->argument('name') ?: 'Test User';
        $code = $this->argument('code') ?: '123456';

        $this->info("Testing Twilio SMS to $phone with code $code");

        $smsService = app(SmsService::class);
        $result = $smsService->sendConfirmationCode($phone, $name, $code);

        if ($result) {
            $this->info('SMS sent successfully!');
        } else {
            $this->error('Failed to send SMS. Check Twilio configuration.');
        }
    }
}