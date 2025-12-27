<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'quote' => 'Hier kann ich endlich ich selbst sein, ohne Maske und ohne Leistungsdruck. Der Kreis hat mir einen Raum gegeben, in dem ich mich verletzlich zeigen darf.',
                'author_name' => 'Michael',
                'role' => 'Teilnehmer seit 2023',
                'sort_order' => 1,
            ],
            [
                'quote' => 'Der Kreis hat mir gezeigt, dass ich mit meinen Gefühlen und Zweifeln nicht alleine bin. Das hat mir unglaublich viel Kraft gegeben.',
                'author_name' => null,
                'role' => null,
                'sort_order' => 2,
            ],
            [
                'quote' => 'Eine Oase der Ehrlichkeit in einer Welt voller Fassaden. Hier wird nicht geurteilt, sondern zugehört.',
                'author_name' => 'Stefan',
                'role' => 'Teilnehmer seit 2022',
                'sort_order' => 3,
            ],
            [
                'quote' => 'Hier habe ich gelernt, dass Verletzlichkeit keine Schwäche ist, sondern der Mut, sich zu zeigen wie man wirklich ist.',
                'author_name' => null,
                'role' => null,
                'sort_order' => 4,
            ],
            [
                'quote' => 'Zum ersten Mal habe ich Männer kennengelernt, die wirklich zuhören können. Das hat meine Sicht auf Männlichkeit komplett verändert.',
                'author_name' => 'Thomas',
                'role' => 'Gründungsmitglied',
                'sort_order' => 5,
            ],
            [
                'quote' => 'Der Kreis ist ein Raum, in dem ich mich fallen lassen kann. Hier muss ich nicht funktionieren oder stark sein.',
                'author_name' => null,
                'role' => 'Teilnehmer seit 2024',
                'sort_order' => 6,
            ],
        ];

        foreach ($testimonials as $testimonialData) {
            Testimonial::create([
                ...$testimonialData,
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
