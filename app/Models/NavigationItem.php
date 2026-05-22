<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NavigationItem extends Model
{
    use ClearsResponseCache;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'navigation_id',
        'parent_id',
        'label',
        'url',
        'route_name',
        'route_params',
        'anchor',
        'target',
        'order',
        'is_active',
        'icon',
        'css_class',
        'data_attributes',
    ];

    protected function casts(): array
    {
        return [
            'route_params' => 'array',
            'data_attributes' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function navigation(): BelongsTo
    {
        return $this->belongsTo(Navigation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'parent_id')->orderBy('order');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the computed URL for this navigation item.
     * Prioritizes route_name > url, and appends anchor if present.
     */
    public function getComputedUrlAttribute(): string
    {
        $url = '';

        if ($this->route_name) {
            try {
                $params = $this->route_params ?? [];
                $url = route($this->route_name, $params);
            } catch (\Exception) {
                $url = $this->url ?? '#';
            }
        } else {
            $url = $this->url ?? '#';
        }

        if ($this->anchor) {
            $url .= '#' . ltrim($this->anchor, '#');
        }

        return $url;
    }

    /**
     * Get data attributes as string for HTML output
     * Validates and sanitizes attribute keys to prevent malicious input
     */
    public function getDataAttributesStringAttribute(): string
    {
        if (empty($this->data_attributes)) {
            return '';
        }

        $attributes = [];
        foreach ($this->data_attributes as $key => $value) {
            // Sanitize key: remove data- prefix if present, then validate format
            $sanitizedKey = preg_replace('/^data-/', '', $key);

            // Only allow safe characters: letters, numbers, underscores, dots, colons, hyphens
            if (!preg_match('/^[a-zA-Z0-9_.:-]+$/', $sanitizedKey)) {
                continue; // Skip invalid keys
            }

            $attributes[] = sprintf('data-%s="%s"', e($sanitizedKey), e($value));
        }

        return implode(' ', $attributes);
    }
}
