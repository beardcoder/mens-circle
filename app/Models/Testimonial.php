<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpStaticAnalysis\Attributes\TemplateUse;

#[TemplateUse('HasFactory<\Database\Factories\TestimonialFactory>')]
class Testimonial extends Model
{
    use HasFactory;
    use ClearsResponseCache;
    use SoftDeletes;

    protected $fillable = [
        'quote',
        'author_name',
        'email',
        'role',
        'is_published',
        'published_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include published testimonials.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Default ordering by sort_order and created_at.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
        });
    }
}
