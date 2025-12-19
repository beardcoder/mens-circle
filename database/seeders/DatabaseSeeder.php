<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user for Filament
        User::create([
            'name' => 'Admin',
            'email' => 'admin@mens-circle.de',
            'password' => Hash::make('password'),
        ]);

        // Create homepage with content blocks
        Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'published_at' => now(),
            'content_blocks' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'subtitle' => 'Straubing / Niederbayern',
                        'title' => "Ein Raum für\nechte Begegnung",
                        'description' => 'Der Männerkreis ist ein geschützter Ort, an dem du dich zeigen kannst, wie du wirklich bist. Authentischer Austausch. Ehrliche Gemeinschaft. Persönliches Wachstum.',
                    ],
                ],
                [
                    'type' => 'text_section',
                    'data' => [
                        'eyebrow' => 'Über uns',
                        'title' => 'Was ist der Männerkreis?',
                        'content' => '<p>Der Männerkreis Straubing ist ein regelmäßiger Treffpunkt für Männer, die nach echtem Austausch und authentischer Verbindung suchen. In einer Welt, die oft von Oberflächlichkeit geprägt ist, schaffen wir einen geschützten Raum für tiefe Begegnungen.</p><p>Hier kannst du dich zeigen, wie du wirklich bist – mit deinen Stärken, Zweifeln, Freuden und Herausforderungen. Ohne Masken, ohne Bewertung, einfach echt.</p>',
                    ],
                ],
                [
                    'type' => 'value_items',
                    'data' => [
                        'eyebrow' => 'Unsere Werte',
                        'title' => 'Was uns wichtig ist',
                        'items' => [
                            [
                                'number' => '01',
                                'title' => 'Authentizität',
                                'description' => 'Sei du selbst, ohne Masken und Fassaden. Hier darfst du zeigen, wie es dir wirklich geht.',
                            ],
                            [
                                'number' => '02',
                                'title' => 'Vertraulichkeit',
                                'description' => 'Was im Kreis besprochen wird, bleibt im Kreis. Ein geschützter Raum für offene Worte.',
                            ],
                            [
                                'number' => '03',
                                'title' => 'Wertschätzung',
                                'description' => 'Jeder wird gehört und respektiert. Keine Bewertung, keine Lösungen – nur echtes Zuhören.',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'journey_steps',
                    'data' => [
                        'eyebrow' => 'Die Reise',
                        'title' => 'So läuft ein Treffen ab',
                        'steps' => [
                            [
                                'number' => '1',
                                'title' => 'Ankommen',
                                'description' => 'Gemeinsam beginnen wir mit einer kurzen Einstimmung in den Raum.',
                            ],
                            [
                                'number' => '2',
                                'title' => 'Austausch',
                                'description' => 'Jeder bekommt Zeit, sich mitzuteilen. Du entscheidest, was du teilen möchtest.',
                            ],
                            [
                                'number' => '3',
                                'title' => 'Integration',
                                'description' => 'Wir schließen mit einem gemeinsamen Abschluss und Reflexion.',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'faq',
                    'data' => [
                        'eyebrow' => 'Häufige Fragen',
                        'title' => 'Was du wissen solltest',
                        'items' => [
                            [
                                'question' => 'Wer kann teilnehmen?',
                                'answer' => 'Jeder Mann ist willkommen, unabhängig von Alter, Herkunft oder Lebenssituation. Es ist keine Vorerfahrung nötig.',
                            ],
                            [
                                'question' => 'Was kostet die Teilnahme?',
                                'answer' => 'Die Teilnahme erfolgt auf Spendenbasis. Jeder gibt, was für ihn stimmig ist und möglich ist.',
                            ],
                            [
                                'question' => 'Wie oft finden die Treffen statt?',
                                'answer' => 'Wir treffen uns in der Regel einmal im Monat. Die genauen Termine findest du auf der Event-Seite.',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'newsletter',
                    'data' => [
                        'eyebrow' => 'Newsletter',
                        'title' => 'Bleib auf dem Laufenden',
                        'text' => 'Erhalte Infos zu kommenden Treffen und Inspirationen für deinen Weg.',
                    ],
                ],
            ],
            'meta' => [
                'description' => 'Männerkreis Straubing – Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.',
            ],
        ]);

        // Create sample event
        Event::create([
            'title' => 'Männerkreis Straubing – Januar 2025',
            'slug' => 'maennerkreis-januar-2025',
            'description' => "Der Männerkreis ist ein regelmäßiges Treffen von Männern, die sich nach echtem Austausch und authentischer Verbindung sehnen. In einem geschützten Rahmen teilen wir unsere Erfahrungen, Herausforderungen und Erkenntnisse.\n\nEs ist keine Vorerfahrung nötig – bringe einfach dich selbst mit, so wie du gerade bist. Wir freuen uns auf dich!",
            'event_date' => now()->addDays(14),
            'start_time' => '19:00',
            'end_time' => '21:30',
            'location' => 'Straubing',
            'location_details' => 'Die genaue Adresse erhältst du nach deiner Anmeldung per E-Mail.',
            'max_participants' => 8,
            'cost_basis' => 'Auf Spendenbasis',
            'is_published' => true,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@mens-circle.de / password');
    }
}
