# MoonShine Quick Reference

## Access URLs

| System | URL | Auth Table | Users |
|--------|-----|------------|-------|
| Filament | `/admin` | `users` | App users |
| MoonShine | `/moonshine` | `moonshine_users` | MoonShine users |

## Quick Commands

### Setup (First Time)
```bash
# Install dependencies
composer install

# Run MoonShine setup
./setup-moonshine.sh

# Or manually:
php artisan moonshine:install
php artisan migrate
php artisan moonshine:user
```

### Development
```bash
# Start all services
composer run dev

# Start just Laravel
php artisan serve

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### MoonShine Commands
```bash
# Create resource
php artisan moonshine:resource ModelResource

# Create page
php artisan moonshine:page PageName

# Create user
php artisan moonshine:user

# Install/reinstall
php artisan moonshine:install
```

## File Locations

### Configuration
- `config/moonshine.php` - Main MoonShine config
- `config/filament.php` - Filament config (unchanged)
- `.env` - Environment variables

### Code
- `app/MoonShine/Resources/` - MoonShine resources
- `app/MoonShine/Pages/` - MoonShine pages
- `app/Providers/MoonShineServiceProvider.php` - Service provider
- `app/Filament/` - Filament resources (unchanged)

### Documentation
- `docs/moonshine-migration.md` - Migration guide
- `docs/moonshine-testing-checklist.md` - Testing checklist
- `docs/moonshine-technical-spec.md` - Technical details
- `docs/moonshine-implementation-summary.md` - Implementation summary

## Environment Variables

```env
# MoonShine Configuration
MOONSHINE_ROUTE_PREFIX=moonshine
MOONSHINE_TITLE="Männerkreis Niederbayern - MoonShine"
MOONSHINE_LOGO=/logo-color.svg
MOONSHINE_DISK=public
```

## Resource Pattern

```php
<?php

namespace App\MoonShine\Resources;

use MoonShine\Resources\ModelResource;

class YourResource extends ModelResource
{
    protected string $model = YourModel::class;
    protected string $title = 'Your Resources';
    
    public function indexFields(): array
    {
        return [
            // Fields for list view
        ];
    }
    
    public function formFields(): array
    {
        return [
            // Fields for create/edit form
        ];
    }
    
    public function rules($item): array
    {
        return [
            // Validation rules
        ];
    }
}
```

## Field Types Reference

```php
use MoonShine\Fields\*;

Text::make('Label', 'field_name')
    ->required()
    ->placeholder('Enter text');

Textarea::make('Description', 'description')
    ->rows(5);

Number::make('Count', 'count')
    ->min(0)
    ->max(100);

Date::make('Date', 'date')
    ->format('d.m.Y');

Switcher::make('Active', 'is_active')
    ->default(false);

Image::make('Photo', 'photo')
    ->disk('public')
    ->dir('photos');

Relationships\HasMany::make('Items', 'items')
    ->fields([
        Text::make('Name', 'name'),
    ]);
```

## Layout Components

```php
use MoonShine\Decorations\*;

// Grid with columns
Grid::make([
    Column::make([/* fields */])->columnSpan(8),
    Column::make([/* fields */])->columnSpan(4),
]);

// Block with title
Block::make('Block Title', [
    /* fields */
]);

// Tabs
Tabs::make([
    Tab::make('Tab 1', [/* fields */]),
    Tab::make('Tab 2', [/* fields */]),
]);
```

## Menu Configuration

Edit `app/Providers/MoonShineServiceProvider.php`:

```php
protected function menu(): array
{
    return [
        MenuItem::make('Dashboard', 'moonshine.index')
            ->icon('heroicons.home'),
            
        MenuGroup::make('Content', [
            MenuItem::make('Events', EventResource::class)
                ->icon('heroicons.calendar'),
        ]),
        
        MenuItem::make('Custom Page', CustomPage::class),
    ];
}
```

## Common Tasks

### Add a New Resource

1. Create resource:
   ```bash
   php artisan moonshine:resource ParticipantResource
   ```

2. Edit `app/MoonShine/Resources/ParticipantResource.php`

3. Register in `MoonShineServiceProvider`:
   ```php
   protected function resources(): array
   {
       return [
           EventResource::class,
           ParticipantResource::class,
       ];
   }
   ```

4. Add to menu if needed

### Customize Dashboard

Edit `app/MoonShine/Pages/Dashboard.php`:

```php
use MoonShine\Metrics\ValueMetric;

public function components(): array
{
    return [
        ValueMetric::make('Total Items')
            ->value(Model::count())
            ->icon('heroicons.chart-bar'),
    ];
}
```

### Change Branding

Update `.env`:
```env
MOONSHINE_TITLE="Your Title"
MOONSHINE_LOGO=/your-logo.svg
```

### Add Custom Validation

In resource:
```php
public function rules($item): array
{
    return [
        'email' => ['required', 'email', 'unique:table,email'],
        'age' => ['required', 'integer', 'min:18'],
    ];
}
```

## Troubleshooting

### MoonShine Returns 404
```bash
php artisan route:clear
php artisan config:clear
```

### Login Issues
```bash
# Create new user
php artisan moonshine:user

# Check guard config
php artisan tinker
>>> config('moonshine.auth.guard')
```

### Asset Issues
```bash
php artisan moonshine:install
```

### Migration Errors
```bash
php artisan migrate:fresh
php artisan moonshine:install
```

## Testing Checklist

Quick smoke test:

- [ ] Can access `/moonshine`
- [ ] Can login with MoonShine credentials
- [ ] Dashboard loads
- [ ] Can view Events list
- [ ] Can create new Event
- [ ] Filament still works at `/admin`

See `docs/moonshine-testing-checklist.md` for comprehensive tests.

## Support & Resources

- **MoonShine Docs**: https://moonshine-laravel.com/docs
- **Filament Docs**: https://filamentphp.com/docs
- **Laravel Docs**: https://laravel.com/docs
- **Project Docs**: `docs/moonshine-*.md`

## Tips

- **Icons**: Use `heroicons.*` for consistent icons
- **Validation**: Use Laravel validation rules
- **Relationships**: Use `HasMany`, `BelongsTo`, etc.
- **Search**: Define searchable fields in `search()` method
- **Filters**: Add filters in `filters()` method
- **Performance**: Use eager loading with `$with` property

## Example Workflow

1. Create model & migration (if needed)
2. Generate MoonShine resource
3. Define fields (index, form)
4. Add validation rules
5. Register in service provider
6. Add to menu
7. Test CRUD operations
8. Refine as needed

That's it! 🎉
