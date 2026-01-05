<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Page;
use Spatie\ResponseCache\Facades\ResponseCache;

class PageObserver
{
    public function created(Page $page): void
    {
        $this->invalidateCache();
    }

    public function updated(Page $page): void
    {
        $this->invalidateCache();
    }

    public function deleted(Page $page): void
    {
        $this->invalidateCache();
    }

    public function restored(Page $page): void
    {
        $this->invalidateCache();
    }

    public function forceDeleted(Page $page): void
    {
        $this->invalidateCache();
    }

    protected function invalidateCache(): void
    {
        ResponseCache::clear();
    }
}
