<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContentBlock extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'contentable_type',
        'contentable_id',
        'type',
        'data',
        'block_id',
        'order',
    ];

    /**
     * Polymorphe Beziehung zum Parent (z.B. Page, Event)
     */
    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Hole ein spezifisches Media-Objekt für ein Feld
     */
    public function getFieldMedia(string $field): ?Media
    {
        return $this->getMedia('page_blocks')
            ->first(fn (Media $media): bool =>
                $media->getCustomProperty('block_id') === $this->block_id
                && $media->getCustomProperty('field') === $field
            );
    }

    /**
     * Media Collections registrieren
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
