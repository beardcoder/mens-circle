<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content_blocks',
        'meta',
        'is_published',
        'published_at',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getBlockMedia(?string $blockId, string $field): ?Media
    {
        if (! $blockId) {
            return null;
        }

        return $this->getMedia('page_blocks')
            ->first(fn (Media $media): bool => $media->getCustomProperty('block_id') === $blockId
                && $media->getCustomProperty('field') === $field);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('page_blocks')
            ->useDisk('public');
    }

    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
            'meta' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
