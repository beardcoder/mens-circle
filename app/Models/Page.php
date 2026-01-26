<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $title
 * @property string $slug
 * @property array<string, mixed> $meta
 * @property bool $is_published
 * @property ?\Illuminate\Support\Carbon $published_at
 */
class Page extends Model implements HasMedia
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;
    use ClearsResponseCache;

    protected $fillable = ['title', 'slug', 'meta', 'is_published', 'published_at', ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    /**
     * @return HasMany<ContentBlock, $this>
     */
    public function contentBlocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('page_blocks')
->useDisk('public');
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param Builder<Page> $query
     * @return Builder<Page>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Sync content blocks for this page.
     * Handles creation, update, and deletion of blocks and their media.
     *
     * @param array<int, array<string, mixed>> $contentBlocksData
     */
    public function saveContentBlocks(array $contentBlocksData): void
    {
        DB::transaction(function () use ($contentBlocksData): void {
            $existingBlocks = $this->contentBlocks()
->get()
->keyBy('block_id');
            $processedBlockIds = [];

            foreach ($contentBlocksData as $index => $blockData) {
                $data = $blockData['data'] ?? [];
                // Block ID comes from the data array (hidden field)
                $blockId = $data['block_id'] ?? Str::uuid()->toString();

                // Remove block_id from data payload as it's stored in a separate column
                unset($data['block_id']);

                $this->contentBlocks()
->updateOrCreate([
'block_id' => $blockId
], [
                        'type' => $blockData['type'],
                        'data' => $data,
                        'order' => $index,
                    ]);

                $processedBlockIds[] = $blockId;
            }

            // Cleanup removed blocks
            $existingBlocks->each(function ($blockModel) use ($processedBlockIds): void {
                /** @var ContentBlock $blockModel */
                if (!in_array($blockModel->block_id, $processedBlockIds, true)) {
                    // Delete associated media on the Page
                    $this->getMedia('page_blocks')
                        ->filter(fn ($media): bool => $media->getCustomProperty('block_id') === $blockModel->block_id)
                        ->each(fn ($media) => $media->delete());

                    $blockModel->delete();
                }
            });
        });
    }
}
