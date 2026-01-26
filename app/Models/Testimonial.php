<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $quote
 * @property string $author_name
 * @property string $email
 * @property ?string $role
 * @property bool $is_published
 * @property ?Carbon $published_at
 * @property int $sort_order
 */
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;
    use ClearsResponseCache;
    use SoftDeletes;

    protected $fillable = ['quote', 'author_name', 'email', 'role', 'is_published', 'published_at', 'sort_order', ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include published testimonials.
     *
     * @param Builder<Testimonial> $query
     * @return Builder<Testimonial>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Default ordering by sort_order and created_at.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy('sort_order', 'asc')
->orderBy('created_at', 'desc');
        });
    }
}
