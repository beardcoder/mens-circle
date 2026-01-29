<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Database\Factories\ContentBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $page_id
 * @property string $type
 * @property array<string, mixed> $data
 * @property string $block_id
 * @property int $order
 * @property Page $page
 */
class ContentBlock extends Model implements HasMedia
{
    /** @use HasFactory<ContentBlockFactory> */
    use HasFactory;
    use InteractsWithMedia;
    use ClearsResponseCache;

    protected $fillable = ['page_id', 'type', 'data', 'block_id', 'order', ];

    /**
     * Relationship to Page
     *
     * @return BelongsTo<Page, $this>
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
        /** @var Page $page */
        $page = $this->page;

        return $page->getMedia('page_blocks')
            ->first(
                fn(Media $media): bool
                => $media->getCustomProperty('block_id') === $this->block_id
                && $media->getCustomProperty('field') === $field,
            );
    }

    #[\Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('page_blocks')
            ->useDisk('public');
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'order' => 'integer',
        ];
    }
}
