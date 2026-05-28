<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $title
 * @property string $slug
 * @property array<string, mixed> $meta
 * @property bool $is_published
 * @property ?Carbon $published_at
 */
#[Fillable(['title', 'slug', 'meta', 'is_published', 'published_at'])]
#[UseFactory(PageFactory::class)]
class Page extends Model implements HasMedia
{
    use ClearsResponseCache;

    /** @use HasFactory<PageFactory> */
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

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
        $this->addMediaCollection('page_blocks')->useDisk('public');
    }

    #[Override]
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')->performOnCollections('page_blocks')->format('webp')->quality(85);
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
     * @param Builder<Page> $query
     *
     * @return Builder<Page>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Sync content blocks: upserts each block in order, removes any blocks
     * (and their media) that no longer appear in the payload.
     *
     * @param array<int, array{type: string, data?: array<string, mixed>}> $contentBlocksData
     */
    public function saveContentBlocks(array $contentBlocksData): void
    {
        DB::transaction(function () use ($contentBlocksData): void {
            $keptBlockIds = [];

            foreach ($contentBlocksData as $order => $block) {
                $data = $block['data'] ?? [];
                $blockId = (string) ($data['block_id'] ?? Str::uuid());
                unset($data['block_id']);

                $this->contentBlocks()->updateOrCreate(['block_id' => $blockId], [
                    'type' => $block['type'],
                    'data' => $data,
                    'order' => $order,
                ]);

                $keptBlockIds[] = $blockId;
            }

            $this
                ->contentBlocks()
                ->whereNotIn('block_id', $keptBlockIds)
                ->get()
                ->each(function (ContentBlock $block): void {
                    $this
                        ->getMedia('page_blocks')
                        ->where('custom_properties.block_id', $block->block_id)
                        ->each(static fn(Media $media) => $media->delete());

                    $block->delete();
                });
        });
    }
}
