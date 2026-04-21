<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Settings\GeneralSettings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final readonly class PlanAiEvent
{
    public function __construct(
        private GeneralSettings $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $prompt): array
    {
        $plannedDate = $this->resolvePlannedDate($prompt);
        $monthLabel = $plannedDate->translatedFormat('F Y');
        $topic = Str::of($prompt)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->limit(90, '')
            ->toString();

        return [
            'title' => 'Männerkreis ' . $monthLabel,
            'description' => "KI-Entwurf für den Männerkreis im {$monthLabel}. Fokus: {$topic}.",
            'event_date' => $plannedDate->toDateString(),
            'start_time' => '19:00',
            'end_time' => '21:00',
            'location' => $this->settings->location,
            'max_participants' => $this->settings->event_default_max_participants,
            'cost_basis' => 'Auf Spendenbasis',
            'is_published' => false,
            'planning_notes' => [
                'prompt' => $prompt,
                'tone' => 'deutsch',
                'status' => 'draft',
            ],
        ];
    }

    private function resolvePlannedDate(string $prompt): CarbonImmutable
    {
        $monthMap = [
            'januar' => 1,
            'februar' => 2,
            'märz' => 3,
            'maerz' => 3,
            'april' => 4,
            'mai' => 5,
            'juni' => 6,
            'juli' => 7,
            'august' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'dezember' => 12,
        ];

        $prompt = Str::lower($prompt);
        $now = CarbonImmutable::now()->startOfMonth();

        foreach ($monthMap as $monthName => $month) {
            if (! Str::contains($prompt, $monthName)) {
                continue;
            }

            $year = $now->year;
            if ($month < $now->month) {
                $year++;
            }

            return CarbonImmutable::create($year, $month, 15)->startOfDay();
        }

        return $now->addMonth()->day(15)->startOfDay();
    }
}
