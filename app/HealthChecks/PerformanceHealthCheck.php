<?php

declare(strict_types=1);

namespace App\HealthChecks;

use App\Models\Event;
use App\Models\NewsletterSubscription;
use App\Models\Page;
use App\Models\User;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class PerformanceHealthCheck extends Check
{
    protected int $warningThresholdMs = 1000;

    protected int $failureThresholdMs = 2000;

    public function run(): Result
    {
        $result = Result::make();

        try {
            $start = microtime(true);

            // Simulate typical application load
            User::query()->take(10)->get();
            Event::query()->with('confirmedRegistrations')->take(5)->get();
            Page::query()->where('is_published', true)->take(5)->get();
            NewsletterSubscription::query()->where('status', 'active')->take(10)->get();

            $duration = (int) round((microtime(true) - $start) * 1000);

            $result->meta([
                'response_time_ms' => $duration,
                'warning_threshold_ms' => $this->warningThresholdMs,
                'failure_threshold_ms' => $this->failureThresholdMs,
            ]);

            if ($duration > $this->failureThresholdMs) {
                return $result->failed("Application response slow: {$duration}ms (threshold: {$this->failureThresholdMs}ms)");
            }

            if ($duration > $this->warningThresholdMs) {
                return $result->warning("Application response degraded: {$duration}ms (threshold: {$this->warningThresholdMs}ms)");
            }

            return $result->ok("Response time: {$duration}ms");
        } catch (\Throwable $e) {
            return $result->failed("Performance check failed: {$e->getMessage()}");
        }
    }

    public function warningThreshold(int $milliseconds): self
    {
        $this->warningThresholdMs = $milliseconds;

        return $this;
    }

    public function failureThreshold(int $milliseconds): self
    {
        $this->failureThresholdMs = $milliseconds;

        return $this;
    }
}
