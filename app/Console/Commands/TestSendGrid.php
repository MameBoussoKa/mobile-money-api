<?php

namespace App\Console\Commands;

use App\Services\SendGridService;
use Illuminate\Console\Command;

class TestSendGrid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-send-grid {email?} {name?} {code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SendGrid email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?: 'test@example.com';
        $name = $this->argument('name') ?: 'Test User';
        $code = $this->argument('code') ?: '123456';

        $this->info("Testing SendGrid email to $email with code $code");

        $sendGrid = app(SendGridService::class);
        $result = $sendGrid->sendConfirmation($email, $name, $code);

        if ($result) {
            $this->info('Email sent successfully!');
        } else {
            $this->error('Failed to send email. Check API key and configuration.');
        }
    }
}
