# MoonShine Technical Specification

## Architecture Decisions

### 1. Parallel Operation Strategy

**Decision**: Run MoonShine and Filament as completely independent admin panels.

**Rationale**:
- Avoids migration complexity and risk
- Allows gradual evaluation of MoonShine
- Preserves all existing Filament functionality
- Enables comparison of both systems
- Provides fallback if issues arise

**Implementation**:
- Separate route prefixes (`/admin` vs `/moonshine`)
- Separate authentication guards and user tables
- Independent service providers
- Shared data layer (Eloquent models)

### 2. Authentication Separation

**Decision**: Use separate authentication guards and user tables.

**Rationale**:
- Prevents authentication conflicts
- Allows different permission systems
- Enables independent user management
- Reduces coupling between systems

**Implementation**:
```php
// config/moonshine.php
'auth' => [
    'enable' => true,
    'guard' => 'moonshine',        // Separate guard
    'guards' => [
        'moonshine' => [
            'driver' => 'session',
            'provider' => 'moonshine',
        ],
    ],
    'providers' => [
        'moonshine' => [
            'driver' => 'eloquent',
            'model' => MoonshineUser::class,  // Separate model
        ],
    ],
],
```

This creates:
- `moonshine` guard (separate from Filament's `web` guard)
- `moonshine_users` table (separate from `users` table)
- Independent session management

### 3. Resource Pattern

**Decision**: Create one comprehensive example resource (EventResource) rather than multiple basic ones.

**Rationale**:
- Demonstrates full MoonShine capabilities
- Shows best practices for complex forms
- Provides template for future resources
- Proves concept thoroughly

**Features Implemented**:
- Full CRUD operations
- Grid layout with columns
- Image upload functionality
- Date/time handling
- Relationships (HasMany)
- Validation rules
- Search and filters
- Responsive design

### 4. Service Provider Organization

**Decision**: Single dedicated MoonShineServiceProvider extending MoonShine's base provider.

**Rationale**:
- Follows Laravel conventions
- Separates MoonShine concerns
- Easy to locate and modify
- Clear ownership of MoonShine config

**Structure**:
```php
class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    protected function resources(): array { }  // Register resources
    protected function menu(): array { }       // Define menu
    protected function theme(): array { }      // Customize theme
    public function boot(): void { }           // Bootstrap
    public function register(): void { }       // Register services
}
```

### 5. Configuration Strategy

**Decision**: Comprehensive config file with sensible defaults and ENV overrides.

**Rationale**:
- Flexibility for different environments
- Easy customization without code changes
- Follows Laravel patterns
- Documented inline

**Key Configurations**:
```php
// Route prefix
'route' => ['prefix' => env('MOONSHINE_ROUTE_PREFIX', 'moonshine')],

// Branding
'title' => env('MOONSHINE_TITLE', 'Männerkreis Niederbayern - MoonShine'),
'logo' => env('MOONSHINE_LOGO', '/logo-color.svg'),

// Localization
'locale' => 'de',
'locales' => ['de', 'en'],

// Separate disk for uploads
'disk' => env('MOONSHINE_DISK', 'public'),
```

## Code Organization

### Directory Structure

```
app/
├── MoonShine/
│   ├── Pages/
│   │   └── Dashboard.php          # Custom dashboard with metrics
│   └── Resources/
│       └── EventResource.php      # Event CRUD resource
├── Providers/
│   ├── Filament/
│   │   └── AdminPanelProvider.php # Existing Filament (unchanged)
│   └── MoonShineServiceProvider.php # New MoonShine provider
```

**Rationale**:
- Clear separation of MoonShine code
- Easy to locate MoonShine-specific files
- Mirrors Filament's structure
- Scalable for future resources

### Resource Implementation Pattern

EventResource follows MoonShine's resource pattern:

```php
class EventResource extends ModelResource
{
    protected string $model = Event::class;
    protected string $title = 'Events';
    protected string $column = 'title';
    
    public function indexFields(): array { }    // List view fields
    public function formFields(): array { }     // Create/edit form
    public function detailFields(): array { }   // Detail view
    public function search(): array { }         // Searchable fields
    public function filters(): array { }        // Index filters
    public function rules($item): array { }     // Validation
}
```

**Benefits**:
- Declarative approach
- Type-safe with strict types
- Self-documenting
- Easy to extend

## Key Technical Decisions

### 1. Form Layout Design

**Grid System**:
```php
Grid::make([
    Column::make([ /* Main content */ ])->columnSpan(8),
    Column::make([ /* Sidebar */ ])->columnSpan(4),
])
```

**Rationale**:
- Responsive design
- Logical grouping
- Professional appearance
- Matches Filament's approach

### 2. Field Types

Carefully selected field types for Event model:

| Field | Type | Rationale |
|-------|------|-----------|
| title | Text | Simple string input |
| slug | Text (readonly) | Auto-generated, display only |
| event_date | Date | Native date picker |
| start_time/end_time | Date | Time-specific handling |
| description | Textarea | Multi-line text |
| image | Image | File upload with validation |
| max_participants | Number | Integer with min validation |
| is_published | Switcher | Boolean toggle |
| registrations | HasMany | Relationship display |

### 3. Validation Strategy

**Inline Validation**:
```php
public function rules($item): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'event_date' => ['required', 'date'],
        'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        // ...
    ];
}
```

**Rationale**:
- Centralized validation
- Reusable across create/update
- Laravel validation rules
- Type-safe

### 4. Dashboard Metrics

**ValueMetric Components**:
```php
ValueMetric::make('Total Events')
    ->value(Event::count())
    ->icon('heroicons.calendar')
```

**Rationale**:
- Quick overview
- Real-time data
- Visual appeal
- Hero icons integration

## Security Considerations

### 1. Separate Authentication

✅ Different guards prevent session hijacking
✅ Separate password hashing
✅ Independent remember tokens
✅ No cross-authentication vulnerabilities

### 2. CSRF Protection

✅ Laravel's CSRF middleware active
✅ Separate sessions for each panel
✅ Token validation on all forms

### 3. Authorization

✅ MoonShine's built-in authorization
✅ Role-based access control available
✅ Resource-level permissions

### 4. Input Validation

✅ Server-side validation rules
✅ Type declarations (strict_types)
✅ Laravel's validation system
✅ XSS protection via Blade escaping

## Performance Considerations

### 1. Eager Loading

```php
protected array $with = ['registrations'];
```

**Benefit**: Prevents N+1 queries when displaying relationships.

### 2. Query Optimization

- Only load necessary fields in list view
- Pagination enabled by default
- Searchable fields indexed (should be)

### 3. Caching

```php
'cache' => 'array',  // In-memory cache for metadata
```

**Benefit**: Reduces repeated filesystem/database calls.

### 4. Asset Loading

- MoonShine assets loaded separately from Filament
- No bundling conflicts
- Lazy loading where possible

## Testing Strategy

### Unit Tests (Recommended)

```php
// Test resource configuration
public function test_event_resource_has_correct_model()
{
    $resource = new EventResource();
    $this->assertEquals(Event::class, $resource->getModel());
}

// Test validation rules
public function test_event_resource_validation_rules()
{
    $resource = new EventResource();
    $rules = $resource->rules(new Event());
    $this->assertArrayHasKey('title', $rules);
}
```

### Integration Tests (Recommended)

```php
// Test routes
public function test_moonshine_routes_are_registered()
{
    $this->get('/moonshine')->assertStatus(302); // Redirect to login
}

// Test authentication separation
public function test_filament_and_moonshine_auth_are_separate()
{
    // Login to Filament
    $this->actingAs($user, 'web');
    
    // Verify not logged into MoonShine
    $this->assertGuest('moonshine');
}
```

### Browser Tests (Manual)

See `docs/moonshine-testing-checklist.md` for comprehensive manual testing.

## Migration Path (Future)

If full migration from Filament to MoonShine is desired:

### Phase 1: Expand Resources
- Port all Filament resources to MoonShine
- Maintain parallel functionality
- Test thoroughly

### Phase 2: User Migration
- Script to migrate users
- Or implement dual-login
- Or keep separate (if preferred)

### Phase 3: Deprecate Filament
- Redirect `/admin` to `/moonshine`
- Remove Filament package
- Update documentation

**Note**: This is NOT part of current implementation.

## Maintenance Guidelines

### Adding New Resources

1. Generate resource:
   ```bash
   php artisan moonshine:resource ModelResource
   ```

2. Implement methods:
   - indexFields()
   - formFields()
   - rules()

3. Register in MoonShineServiceProvider:
   ```php
   protected function resources(): array
   {
       return [
           EventResource::class,
           NewResource::class,  // Add here
       ];
   }
   ```

4. Add to menu if needed:
   ```php
   protected function menu(): array
   {
       return [
           MenuItem::make('New Model', NewResource::class),
       ];
   }
   ```

### Updating Configuration

All environment-specific config should go in `.env`:
```env
MOONSHINE_ROUTE_PREFIX=custom-admin
MOONSHINE_TITLE="Custom Title"
MOONSHINE_LOGO=/custom-logo.svg
```

### Customizing Theme

Override in MoonShineServiceProvider:
```php
protected function theme(): array
{
    return [
        'colors' => [
            'primary' => '#custom-color',
        ],
    ];
}
```

## Conclusion

This implementation provides:
- ✅ Clean, maintainable code
- ✅ Proper separation of concerns
- ✅ Scalable architecture
- ✅ Well-documented decisions
- ✅ Security best practices
- ✅ Performance optimization
- ✅ Easy to maintain and extend

The parallel operation of Filament and MoonShine demonstrates:
- ✅ Non-intrusive integration
- ✅ Risk mitigation
- ✅ Flexibility for future decisions
- ✅ Clean Laravel architecture
