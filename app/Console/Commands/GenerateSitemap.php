<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Page;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap for the website';

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Add homepage with highest priority
        $sitemap->add(
            Url::create(route('home'))
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        // Add all published events
        Event::published()
            ->select('slug', 'updated_at')
            ->latest('event_date')
            ->get()
            ->each(function (Event $event) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('event.show.slug', $event->slug))
                        ->setLastModificationDate($event->updated_at ?? now())
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        // Add all published pages
        Page::published()
            ->select('slug', 'updated_at')
            ->latest('published_at')
            ->get()
            ->each(function (Page $page) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('page.show', $page->slug))
                        ->setLastModificationDate($page->updated_at ?? now())
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info('Sitemap generated successfully at: '.$path);

        return self::SUCCESS;
    }
}
