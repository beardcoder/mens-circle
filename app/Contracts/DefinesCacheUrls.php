<?php

declare(strict_types=1);

namespace App\Contracts;

interface DefinesCacheUrls
{
    /**
     * Return the full URLs whose cached responses should be invalidated
     * when this model is created, updated, or deleted.
     *
     * @return array<string>
     */
    public function getCacheUrls(): array;
}
