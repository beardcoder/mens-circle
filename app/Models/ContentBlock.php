<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContentBlock extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'page_id',
        'type',
        'data',
        'block_id',
        'order',
    ];

    /**
     * Relationship to Page
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get specific media object for a field
     */
    public function getFieldMedia(string $field): ?Media
    {
        // Media is stored on the Page model
        return $this->page->getMedia('page_blocks')
            ->first(
                fn (Media $media): bool =>
                $media->getCustomProperty('block_id') === $this->block_id
                && $media->getCustomProperty('field') === $field
            );
    }

    /**
     * Register Media Collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('page_blocks')
            ->useDisk('public');
    }

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'order' => 'integer',
        ];
    }
}
