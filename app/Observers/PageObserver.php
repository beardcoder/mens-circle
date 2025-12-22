<?php

namespace App\Observers;

use App\Models\ContentBlock;
use App\Models\Page;

class PageObserver
{
    /**
     * Handle the Page "saved" event (after create/update).
     */
    public function saved(Page $page): void
    {
        $this->syncContentBlocks($page);
    }

    /**
     * Handle the Page "created" event.
     */
    public function created(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Handle the Page "updated" event.
     */
    public function updated(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Handle the Page "restored" event.
     */
    public function restored(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Handle the Page "force deleted" event.
     */
    public function forceDeleted(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Sync content_blocks JSON to ContentBlock models
     */
    protected function syncContentBlocks(Page $page): void
    {
        if (! $page->content_blocks || ! is_array($page->content_blocks)) {
            return;
        }

        // Delete existing content blocks
        $page->contentBlocks()->delete();

        // Create new content blocks from JSON
        foreach ($page->content_blocks as $index => $block) {
            if (! isset($block['type']) || ! isset($block['data'])) {
                continue;
            }

            $contentBlock = $page->contentBlocks()->create([
                'type' => $block['type'],
                'data' => $block['data'],
                'order' => $index,
            ]);

            // Handle media files (background_image for hero, photo for moderator)
            $this->syncBlockMedia($contentBlock, $block['data']);
        }
    }

    /**
     * Sync media files from FileUpload to Media Library
     */
    protected function syncBlockMedia(ContentBlock $block, array $data): void
    {
        // Handle hero background_image
        if ($block->type === 'hero' && isset($data['background_image'])) {
            $this->attachMedia($block, $data['background_image']);
        }

        // Handle moderator photo
        if ($block->type === 'moderator' && isset($data['photo'])) {
            $this->attachMedia($block, $data['photo']);
        }
    }

    /**
     * Attach media file to content block
     */
    protected function attachMedia(ContentBlock $block, string $filePath): void
    {
        if (empty($filePath)) {
            return;
        }

        $fullPath = storage_path('app/public/'.$filePath);

        if (file_exists($fullPath)) {
            $block->addMedia($fullPath)
                ->preservingOriginal()
                ->toMediaCollection('images');
        }
    }

    /**
     * Clear page cache
     */
    protected function clearCache(Page $page): void
    {
        cache()->forget("page.{$page->slug}");

        if ($page->slug === 'home') {
            cache()->forget('page.home');
        }
    }
}
