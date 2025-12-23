<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'location_details',
        'max_participants',
        'cost_basis',
        'is_published',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->event_date->format('Y-m-d'))
            ->saveSlugsTo('slug');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function confirmedRegistrations(): HasMany
    {
        return $this->registrations()->where('status', 'confirmed');
    }

    public function availableSpots(): int
    {
        return $this->max_participants - $this->confirmedRegistrations()->count();
    }

    public function isFull(): bool
    {
        return $this->availableSpots() <= 0;
    }
}
