<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
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
        'meta',
        'is_published',
        'published_at',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    /**
     * Content Blocks Beziehung (sortiert)
     */
    public function contentBlocks()
    {
        return $this->hasMany(ContentBlock::class)->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('page_blocks')->useDisk('public');
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Sync content blocks for this page.
     * Handles creation, update, and deletion of blocks and their media.
     *
     * @param array $contentBlocksData
     * @return void
     */
    public function saveContentBlocks(array $contentBlocksData): void
    {
        DB::transaction(function () use ($contentBlocksData) {
            $existingBlocks = $this->contentBlocks()->get()->keyBy('block_id');
            $processedBlockIds = [];

            foreach ($contentBlocksData as $index => $blockData) {
                $data = $blockData['data'] ?? [];
                // Block ID comes from the data array (hidden field)
                $blockId = $data['block_id'] ?? Str::uuid()->toString();
                
                // Remove block_id from data payload as it's stored in a separate column
                unset($data['block_id']);

                $this->contentBlocks()->updateOrCreate(
                    ['block_id' => $blockId],
                    [
                        'type' => $blockData['type'],
                        'data' => $data,
                        'order' => $index,
                    ]
                );

                $processedBlockIds[] = $blockId;
            }

            // Cleanup removed blocks
            $existingBlocks->each(function (ContentBlock $block) use ($processedBlockIds) {
                if (!in_array($block->block_id, $processedBlockIds)) {
                    // Delete associated media on the Page
                    $this->getMedia('page_blocks')
                        ->filter(fn ($media) => $media->getCustomProperty('block_id') === $block->block_id)
                        ->each(fn ($media) => $media->delete());

                    $block->delete();
                }
            });
        });
    }
}
