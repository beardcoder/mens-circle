<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Impressum page
        $impressum = Page::create([
            'title' => 'Impressum',
            'slug' => 'impressum',
            'is_published' => true,
            'published_at' => now(),
            'meta' => [
                'description' => 'Impressum und rechtliche Angaben des Männerkreis Niederbayern/ Straubing',
                'robots' => 'noindex, nofollow',
            ],
        ]);

        $impressum->contentBlocks()->create([
            'type' => 'text_section',
            'data' => [
                'title' => 'Impressum',
                'content' => '<p><strong>Angaben gemäß § 5 TMG:</strong></p>'.
                    '<p>Markus Sommer<br>Männerkreis Niederbayern/ Straubing<br>Musterstraße 1<br>94315 Straubing</p>'.
                    '<p><strong>Kontakt:</strong><br>E-Mail: hallo@mens-circle.de</p>'.
                    '<p><strong>Hinweis:</strong> Bitte vervollständigen Sie diese Angaben vor Go-Live gemäß Ihren rechtlichen Anforderungen.</p>',
            ],
            'block_id' => (string) Str::uuid(),
            'order' => 0,
        ]);

        // Create Datenschutz page
        $datenschutz = Page::create([
            'title' => 'Datenschutzerklärung',
            'slug' => 'datenschutz',
            'is_published' => true,
            'published_at' => now(),
            'meta' => [
                'description' => 'Datenschutzerklärung des Männerkreis Niederbayern/ Straubing gemäß DSGVO',
                'robots' => 'noindex, nofollow',
            ],
        ]);

        $datenschutz->contentBlocks()->create([
            'type' => 'text_section',
            'data' => [
                'title' => 'Datenschutzerklärung',
                'content' => '<h3>1. Datenschutz auf einen Blick</h3>'.
                    '<p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen.</p>'.
                    '<h3>2. Allgemeine Hinweise und Pflichtinformationen</h3>'.
                    '<p>Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>'.
                    '<h3>3. Newsletter</h3>'.
                    '<p>Wenn Sie den auf der Website angebotenen Newsletter beziehen möchten, benötigen wir von Ihnen eine E-Mail-Adresse sowie Informationen, welche uns die Überprüfung gestatten, dass Sie der Inhaber der angegebenen E-Mail-Adresse sind.</p>'.
                    '<p><strong>Hinweis:</strong> Bitte vervollständigen Sie diese Datenschutzerklärung vor Go-Live gemäß DSGVO-Anforderungen.</p>',
            ],
            'block_id' => (string) Str::uuid(),
            'order' => 0,
        ]);
    }
}
