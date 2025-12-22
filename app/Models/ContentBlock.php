<?php

namespace App\Models;

use App\Enums\ContentBlockType;
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
        'order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentBlockType::class,
            'data' => 'array',
            'order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->width(200)
                    ->height(200)
                    ->format('webp')
                    ->quality(85);

                $this
                    ->addMediaConversion('responsive')
                    ->width(1200)
                    ->format('webp')
                    ->quality(85)
                    ->performOnCollections('images');

                $this
                    ->addMediaConversion('responsive-avif')
                    ->width(1200)
                    ->format('avif')
                    ->quality(85)
                    ->performOnCollections('images');
            });
    }
}
