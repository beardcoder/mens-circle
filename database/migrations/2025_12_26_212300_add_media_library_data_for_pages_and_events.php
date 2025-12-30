<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migratePages();
        $this->migrateEvents();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }

    private function migratePages(): void
    {
        Page::query()->chunkById(50, function ($pages): void {
            foreach ($pages as $page) {
                $blocks = $page->content_blocks;

                if (! is_array($blocks)) {
                    continue;
                }

                $page->loadMissing('media');
                $mediaItems = $page->getMedia('page_blocks');
                $hasUpdates = false;

                foreach ($blocks as $index => $block) {
                    if (! is_array($block)) {
                        continue;
                    }

                    $data = $block['data'] ?? [];

                    if (! is_array($data)) {
                        continue;
                    }

                    if (empty($data['block_id'])) {
                        $data['block_id'] = (string) Str::uuid();
                        $hasUpdates = true;
                    }

                    $blockId = $data['block_id'];
                    $fields = match ($block['type'] ?? null) {
                        'hero' => ['background_image'],
                        'moderator' => ['photo'],
                        default => [],
                    };

                    foreach ($fields as $field) {
                        $path = $data[$field] ?? null;

                        if (! is_string($path) || $path === '') {
                            continue;
                        }

                        $path = $this->normalizePublicPath($path);

                        $existing = $mediaItems->first(
                            fn (Media $media): bool => $media->getCustomProperty('block_id') === $blockId
                                && $media->getCustomProperty('field') === $field,
                        );

                        if ($existing) {
                            continue;
                        }

                        if (! Storage::disk('public')->exists($path)) {
                            continue;
                        }

                        $storedMedia = $page->addMedia(Storage::disk('public')->path($path))
                            ->withResponsiveImages()
                            ->withCustomProperties([
                                'block_id' => $blockId,
                                'field' => $field,
                            ])
                            ->toMediaCollection('page_blocks', 'public');

                        $mediaItems->push($storedMedia);
                    }

                    $block['data'] = $data;
                    $blocks[$index] = $block;
                }

                if ($hasUpdates) {
                    $page->forceFill([
                        'content_blocks' => $blocks,
                    ])->saveQuietly();
                }
            }
        });
    }

    private function migrateEvents(): void
    {
        Event::query()->chunkById(50, function ($events): void {
            foreach ($events as $event) {
                $path = $event->image;

                if (! is_string($path) || $path === '') {
                    continue;
                }

                $path = $this->normalizePublicPath($path);

                if ($event->getFirstMedia('event_image')) {
                    continue;
                }

                if (! Storage::disk('public')->exists($path)) {
                    continue;
                }

                $event->addMedia(Storage::disk('public')->path($path))
                    ->withResponsiveImages()
                    ->toMediaCollection('event_image', 'public');
            }
        });
    }

    private function normalizePublicPath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return substr($path, strlen('storage/'));
        }

        return $path;
    }
};
