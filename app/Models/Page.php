<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model
{
    use HasSlug;
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
