<?php

namespace App\Models;

use App\Enums\ContentBlockType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

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
            ->withResponsiveImages();
    }
}
