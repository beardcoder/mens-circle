<?php

declare(strict_types=1);

namespace App\Contracts;

interface DefinesCacheUrls
{
    /**
     * Return the full URLs whose cached responses should be invalidated
     * when this model is created, updated, or deleted.
     *
     * @return list<string>
     */
    public function getCacheUrls(): array;

    /**
     * Return additional cache keys to forget on model changes.
     *
     * @return list<string>
     */
    public function getCacheKeys(): array;
}
