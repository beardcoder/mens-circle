<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sentry\Laravel\Facade as Sentry;

class SentryTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentry:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Sentry error reporting integration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('sentry.dsn')) {
            $this->error('Sentry DSN is not configured. Please set SENTRY_LARAVEL_DSN in your .env file.');

            return self::FAILURE;
        }

        $this->info('Testing Sentry integration...');

        // Send a test message
        Sentry::captureMessage('Sentry test message from Laravel application');
        $this->info('✓ Test message sent to Sentry');

        // Test exception capture
        try {
            throw new \Exception('Sentry test exception - this is intentional');
        } catch (\Exception $e) {
            Sentry::captureException($e);
            $this->info('✓ Test exception sent to Sentry');
        }

        $this->newLine();
        $this->info('Sentry test completed! Check your Sentry dashboard for the test message and exception.');
        $this->info('Dashboard: https://sentry.io/');

        return self::SUCCESS;
    }
}
