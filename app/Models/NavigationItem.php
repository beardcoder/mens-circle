<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Traits\ClearsResponseCache;
use Database\Factories\NavigationItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property NavigationLocation $location
 * @property string $label
 * @property string $url
 * @property ?NavigationCondition $condition
 * @property bool $open_in_new_tab
 * @property bool $is_cta
 * @property bool $is_visible
 * @property ?string $umami_event_target
 * @property int $sort
 */
#[Fillable([
    'location',
    'label',
    'url',
    'condition',
    'open_in_new_tab',
    'is_cta',
    'is_visible',
    'umami_event_target',
    'sort',
])]
#[UseFactory(NavigationItemFactory::class)]
class NavigationItem extends Model
{
    /** @use HasFactory<NavigationItemFactory> */
    use HasFactory;
    use ClearsResponseCache;

    /**
     * @param Builder<NavigationItem> $query
     *
     * @return Builder<NavigationItem>
     */
    public function scopeForLocation(Builder $query, NavigationLocation $location): Builder
    {
        return $query->where('location', $location->value)->where('is_visible', true)->orderBy('sort')->orderBy('id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'location' => NavigationLocation::class,
            'condition' => NavigationCondition::class,
            'open_in_new_tab' => 'boolean',
            'is_cta' => 'boolean',
            'is_visible' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
