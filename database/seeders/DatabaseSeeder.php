<?php

namespace Database\Seeders;

use App\Enums\ContentBlockType;
use App\Models\ContentBlock;
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

        // Create homepage
        $homePage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'published_at' => now(),
            'meta' => [
                'description' => 'Männerkreis Straubing – Authentischer Austausch, Gemeinschaft und persönliches Wachstum für Männer in Niederbayern.',
            ],
        ]);

        // Create content blocks for homepage
        $contentBlocks = [
            [
                'type' => ContentBlockType::Hero,
                'data' => [
                    'label' => 'Straubing / Niederbayern',
                    'title' => '<span class="hero__title-line">Ein Raum für</span><span class="hero__title-line"><span class="text-italic">echte</span> Begegnung</span>',
                    'description' => 'Der Männerkreis ist ein geschützter Ort, an dem du dich zeigen kannst, wie du wirklich bist. Authentischer Austausch. Ehrliche Gemeinschaft. Persönliches Wachstum.',
                    'button_text' => 'Dabei sein',
                    'button_link' => '/termin',
                ],
                'order' => 1,
            ],
            [
                'type' => ContentBlockType::Intro,
                'data' => [
                    'eyebrow' => 'Was uns verbindet',
                    'title' => 'Was ist ein<br><span class="text-italic">Männerkreis?</span>',
                    'text' => 'Ein Männerkreis ist ein regelmäßiges Treffen von Männern, die sich in einem geschützten Rahmen begegnen möchten. Hier geht es nicht um Smalltalk oder Leistung – sondern um echte Verbindung, ehrliche Worte und das Gefühl, gehört und gesehen zu werden.',
                    'quote' => '„Im Kreis sitzen Männer,<br>die sich trauen,<br>echt zu sein."',
                    'values' => [
                        [
                            'number' => '01',
                            'title' => 'Authentischer Austausch',
                            'description' => 'Hier darfst du sagen, was dich wirklich bewegt – ohne Maske, ohne Rolle.',
                        ],
                        [
                            'number' => '02',
                            'title' => 'Ehrliche Gemeinschaft',
                            'description' => 'Verbindung entsteht, wenn wir uns gegenseitig wirklich zuhören.',
                        ],
                        [
                            'number' => '03',
                            'title' => 'Persönliches Wachstum',
                            'description' => 'Durch Reflexion und Feedback entwickeln wir uns gemeinsam weiter.',
                        ],
                    ],
                ],
                'order' => 2,
            ],
            [
                'type' => ContentBlockType::Moderator,
                'data' => [
                    'eyebrow' => 'Dein Begleiter',
                    'name' => 'Markus<br><span class="light">Sommer</span>',
                    'bio' => '<p>Ich bin Markus, gebürtiger Niederbayer und Gründer des Männerkreises Straubing. Seit Jahren beschäftige ich mich mit der Frage, was es bedeutet, als Mann authentisch zu leben – jenseits von Rollenbildern und gesellschaftlichen Erwartungen.</p><p>Der Männerkreis ist für mich ein Herzensanliegen: Ein Ort, an dem wir uns gegenseitig stärken, herausfordern und unterstützen können.</p>',
                    'quote' => '„Wahre Stärke zeigt sich nicht im Alleingang, sondern in der Bereitschaft, sich anderen zu öffnen."',
                ],
                'order' => 3,
            ],
            [
                'type' => ContentBlockType::JourneySteps,
                'data' => [
                    'eyebrow' => 'Der Weg',
                    'title' => 'Die Reise <span class="text-italic">im Kreis</span>',
                    'subtitle' => 'Jedes Treffen folgt einem natürlichen Rhythmus',
                    'steps' => [
                        [
                            'number' => '1',
                            'title' => 'Ankommen',
                            'description' => 'Wir beginnen mit einer Runde des Ankommens. Jeder teilt kurz, wie er gerade da ist – körperlich, emotional, mental.',
                        ],
                        [
                            'number' => '2',
                            'title' => 'Öffnen',
                            'description' => 'Im geschützten Raum des Kreises öffnen wir uns. Themen, die uns bewegen, finden Raum und Gehör.',
                        ],
                        [
                            'number' => '3',
                            'title' => 'Wachsen',
                            'description' => 'Durch ehrliches Feedback und Spiegelung entstehen neue Perspektiven. Wir lernen von und mit einander.',
                        ],
                        [
                            'number' => '4',
                            'title' => 'Integrieren',
                            'description' => 'Zum Abschluss verankern wir das Erlebte. Was nehmen wir mit? Was setzen wir im Alltag um?',
                        ],
                    ],
                ],
                'order' => 4,
            ],
            [
                'type' => ContentBlockType::Faq,
                'data' => [
                    'eyebrow' => 'Fragen & Antworten',
                    'title' => 'Häufige<br><span class="text-italic">Fragen</span>',
                    'intro' => 'Alles, was du wissen solltest, bevor du zum ersten Mal dabei bist.',
                    'items' => [
                        [
                            'question' => 'Für wen ist der Männerkreis?',
                            'answer' => 'Der Männerkreis ist offen für alle Männer, die sich nach authentischem Austausch und echten Verbindungen sehnen. Es spielt keine Rolle, ob du 25 oder 65 bist, ob du in einer Beziehung lebst oder Single bist. Wichtig ist nur die Bereitschaft, dich auf den Prozess einzulassen und anderen Männern ehrlich und respektvoll zu begegnen.',
                        ],
                        [
                            'question' => 'Wo und wie oft trifft sich der Kreis?',
                            'answer' => 'Wir treffen uns in Straubing – der genaue Ort wird bei der Anmeldung bekannt gegeben. Die Treffen finden regelmäßig statt, in der Regel alle zwei bis vier Wochen.',
                        ],
                        [
                            'question' => 'Wie läuft ein Treffen ab?',
                            'answer' => 'Ein Treffen dauert etwa 2-3 Stunden. Wir sitzen im Kreis – das ist mehr als nur eine Sitzordnung, es ist ein Symbol für Gleichwertigkeit. Der Ablauf folgt einem natürlichen Rhythmus: Ankommen, Öffnen, Wachsen, Integrieren. Es gibt keine starren Regeln, aber Leitlinien wie respektvolles Zuhören und Vertraulichkeit.',
                        ],
                        [
                            'question' => 'Was kostet die Teilnahme?',
                            'answer' => 'Der Männerkreis funktioniert auf Spendenbasis. Das bedeutet: Jeder gibt, was er kann und was ihm die Erfahrung wert ist. Finanzielle Gründe sollen niemanden davon abhalten, Teil des Kreises zu werden.',
                        ],
                        [
                            'question' => 'Ist alles vertraulich?',
                            'answer' => 'Ja, absolut. Vertraulichkeit ist das Fundament des Männerkreises. Alles, was im Kreis geteilt wird, bleibt im Kreis.',
                        ],
                        [
                            'question' => 'Ist das Therapie oder Coaching?',
                            'answer' => 'Nein. Der Männerkreis ist weder Therapie noch Coaching. Es geht nicht darum, Probleme zu lösen oder Ratschläge zu geben. Stattdessen bietet der Kreis einen Raum des Zuhörens und der Verbindung. Bei therapeutischem Bedarf empfehle ich professionelle Hilfe.',
                        ],
                    ],
                ],
                'order' => 5,
            ],
            [
                'type' => ContentBlockType::Newsletter,
                'data' => [
                    'eyebrow' => 'In Verbindung bleiben',
                    'title' => 'Bleib <span class="text-italic">verbunden</span>',
                    'text' => 'Erhalte Informationen zu kommenden Treffen, Impulse zum Thema Männlichkeit und Neuigkeiten aus dem Männerkreis Straubing.',
                ],
                'order' => 6,
            ],
            [
                'type' => ContentBlockType::Cta,
                'data' => [
                    'eyebrow' => 'Nächstes Treffen',
                    'title' => 'Sei beim <span class="text-italic">nächsten</span><br>Mal dabei',
                    'text' => 'Der nächste Männerkreis findet bald statt. Sichere dir deinen Platz und erlebe, was echte Männergemeinschaft bedeutet.',
                    'button_text' => 'Zum Termin & Anmeldung',
                    'button_link' => '/termin',
                ],
                'order' => 7,
            ],
        ];

        foreach ($contentBlocks as $blockData) {
            $homePage->contentBlocks()->create($blockData);
        }

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
