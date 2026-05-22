<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NavigationType;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Navigation extends Model
{
    use ClearsResponseCache;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $navigation): void {
            if (! $navigation->is_active) {
                return;
            }

            $hasConflict = self::query()
                ->where('type', $navigation->type)
                ->where('is_active', true)
                ->when($navigation->exists, fn (Builder $query) => $query->whereKeyNot($navigation->getKey()))
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'type' => ['Only one active navigation per type is allowed.'],
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'type' => NavigationType::class,
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class)->orderBy('order');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, NavigationType $type)
    {
        return $query->where('type', $type);
    }
}
