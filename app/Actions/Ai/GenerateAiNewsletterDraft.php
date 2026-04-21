<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Enums\NewsletterStatus;
use App\Models\Event;
use App\Models\Newsletter;
use App\Services\Ai\AiAuditLogger;
use Illuminate\Support\Str;

final readonly class GenerateAiNewsletterDraft
{
    public function __construct(
        private AiAuditLogger $auditLogger,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Newsletter
    {
        $nextEvent = Event::nextEvent();
        $prompt = trim((string) ($data['prompt'] ?? ''));
        $subject = (string) ($data['subject'] ?? $this->generateSubject($nextEvent));
        $content = (string) ($data['content'] ?? $this->generateContent($nextEvent, $prompt));

        $newsletter = Newsletter::create([
            'subject' => $subject,
            'content' => $content,
            'status' => NewsletterStatus::Draft,
        ]);

        $this->auditLogger->log('ai.newsletter.generated', [
            'newsletter_id' => $newsletter->id,
        ]);

        return $newsletter;
    }

    private function generateSubject(?Event $event): string
    {
        if ($event instanceof Event) {
            return 'Neues Treffen am ' . $event->event_date->translatedFormat('d. F Y');
        }

        return 'Neuigkeiten aus dem Männerkreis Niederbayern';
    }

    private function generateContent(?Event $event, string $prompt): string
    {
        $lines = [
            '<p>Hallo zusammen,</p>',
        ];

        if ($event instanceof Event) {
            $lines[] = '<p>unser nächstes Treffen findet am <strong>' . $event->event_date->translatedFormat('d. F Y') . '</strong> in ' . e((string) $event->location) . ' statt.</p>';
            if ($event->description) {
                $lines[] = '<p>' . e(Str::limit(strip_tags($event->description), 280)) . '</p>';
            }
        }

        if ($prompt !== '') {
            $lines[] = '<p>' . e($prompt) . '</p>';
        }

        $lines[] = '<p>Herzliche Grüße<br>Männerkreis Niederbayern</p>';

        return implode('', $lines);
    }
}
