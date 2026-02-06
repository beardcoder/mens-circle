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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $title
 * @property string $slug
 * @property array<string, mixed> $meta
 * @property bool $is_published
 * @property ?Carbon $published_at
 */
class Page extends Model implements HasMedia
{
    use ClearsResponseCache;

    /** @use HasFactory<PageFactory> */
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'meta', 'is_published', 'published_at'];

    #[Override]
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

    #[Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('page_blocks')
            ->useDisk('public');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Page>  $query
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
     *
     * @return void
     */
    public function saveContentBlocks(array $contentBlocksData): void
    {
        DB::transaction(function () use ($contentBlocksData): void {
            $existingBlocks = $this->contentBlocks()->get()->keyBy('block_id');
            $processedBlockIds = $this->processContentBlocks($contentBlocksData);
            $this->cleanupRemovedBlocks($existingBlocks, $processedBlockIds);
        });
    }

    /**
     * Process and save content blocks, returning array of processed block IDs.
     *
     * @param array<int, array<string, mixed>> $contentBlocksData
     *
     * @return array<int, string>
     */
    private function processContentBlocks(array $contentBlocksData): array
    {
        $processedBlockIds = [];

        foreach ($contentBlocksData as $index => $blockData) {
            $blockId = $this->saveContentBlock($blockData, $index);
            $processedBlockIds[] = $blockId;
        }

        return $processedBlockIds;
    }

    /**
     * Save or update a single content block.
     *
     * @param array<string, mixed> $blockData
     *
     * @return string
     */
    private function saveContentBlock(array $blockData, int $order): string
    {
        /** @var array<string, mixed> $data */
        $data = $blockData['data'] ?? [];
        $blockIdRaw = $data['block_id'] ?? null;
        $blockId = \is_string($blockIdRaw) ? $blockIdRaw : Str::uuid()->toString();

        unset($data['block_id']);

        $this->contentBlocks()->updateOrCreate(
            ['block_id' => $blockId],
            [
                'type' => $blockData['type'],
                'data' => $data,
                'order' => $order,
            ],
        );

        return $blockId;
    }

    /**
     * Remove blocks that are no longer in the content blocks data.
     *
     * @param Collection<string, ContentBlock> $existingBlocks
     * @param array<int, string> $processedBlockIds
     *
     * @return void
     */
    private function cleanupRemovedBlocks($existingBlocks, array $processedBlockIds): void
    {
        $existingBlocks
            ->reject(fn (ContentBlock $block): bool => \in_array($block->block_id, $processedBlockIds, true))
            ->each(function (ContentBlock $block): void {
                $this->deleteBlockMedia($block->block_id);
                $block->delete();
            });
    }

    /**
     * Delete all media associated with a specific block.
     *
     * @return void
     */
    private function deleteBlockMedia(string $blockId): void
    {
        $this->getMedia('page_blocks')
            ->filter(fn ($media): bool => $media->getCustomProperty('block_id') === $blockId)
            ->each(fn ($media) => $media->delete());
    }
}
