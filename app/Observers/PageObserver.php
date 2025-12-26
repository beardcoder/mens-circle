<?php

namespace App\Observers;

use App\Models\Page;

class PageObserver
{
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
     * Clear page cache
     */
    protected function clearCache(Page $page): void
    {
        cache()->forget('page.'.$page->slug);

        if ($page->slug === 'home') {
            cache()->forget('page.home');
        }
    }
}
