# Navigation System

Das Männerkreis Niederbayern Projekt verwendet ein dynamisches Navigation-System basierend auf `spatie/laravel-navigation` mit vollständiger Filament-Integration.

## Übersicht

Das System unterstützt:
- ✅ Mehrere Navigationen (Header, Footer, Legal)
- ✅ Anker-Links (Sprungmarken) für In-Page-Navigation
- ✅ Laravel-Routes mit Parametern
- ✅ Data-Attribute für Analytics (Umami)
- ✅ Verschachtelte Menüs (Parent-Child-Beziehungen)
- ✅ Sortierung und Aktivierung/Deaktivierung
- ✅ Automatische Cache-Invalidierung
- ✅ MCP-Tools für AI-gestützte Verwaltung

## Architektur

### Models

#### Navigation
```php
Navigation {
  id: uuid
  name: string
  type: NavigationType (Header|Footer|Legal)
  is_active: boolean
  items: hasMany(NavigationItem)
}
```

#### NavigationItem
```php
NavigationItem {
  id: uuid
  navigation_id: uuid
  parent_id: uuid|null
  label: string
  url: string|null
  route_name: string|null
  route_params: array|null
  anchor: string|null           # Sprungmarke ohne #
  target: string (_self|_blank)
  order: integer
  is_active: boolean
  icon: string|null
  css_class: string|null
  data_attributes: array|null

  // Computed
  computed_url: string          # Finale URL mit Route + Anchor
  data_attributes_string: string # HTML data-* Attribute
}
```

## Verwendung

### In Blade Templates

```blade
{{-- Header Navigation --}}
@if ($headerNavigation)
  <nav class="nav">
    @foreach ($headerNavigation->activeItems()->rootItems()->get() as $item)
      <a
        href="{{ $item->computed_url }}"
        class="nav__link"
        {!! $item->data_attributes_string !!}
      >
        {{ $item->label }}
      </a>
    @endforeach
  </nav>
@endif

{{-- Mit Navigation-Komponente --}}
<x-navigation :navigation="$headerNavigation" css-class="nav" />
```

### In Controllers/Services

```php
use App\Enums\NavigationType;
use App\Models\Navigation;

// Navigation laden
$headerNav = Navigation::with('items')
    ->active()
    ->ofType(NavigationType::Header)
    ->first();

// Nur aktive Root-Items
$items = $headerNav->activeItems()->rootItems()->get();

// Mit Kindern
$items = $headerNav->items()
    ->with('children')
    ->rootItems()
    ->get();
```

### Neue Navigation erstellen

```php
use App\Enums\NavigationType;
use App\Models\Navigation;
use App\Models\NavigationItem;

$nav = Navigation::create([
    'name' => 'Meine Navigation',
    'type' => NavigationType::Header,
    'is_active' => true,
]);

// Einfacher Link
NavigationItem::create([
    'navigation_id' => $nav->id,
    'label' => 'Home',
    'route_name' => 'home',
    'order' => 1,
    'is_active' => true,
]);

// Mit Anker (Sprungmarke)
NavigationItem::create([
    'navigation_id' => $nav->id,
    'label' => 'FAQ',
    'route_name' => 'home',
    'anchor' => 'faq',  // wird zu: /home#faq
    'order' => 2,
    'is_active' => true,
]);

// Mit Route-Parametern
NavigationItem::create([
    'navigation_id' => $nav->id,
    'label' => 'Impressum',
    'route_name' => 'page.show',
    'route_params' => ['slug' => 'impressum'],
    'order' => 3,
    'is_active' => true,
]);

// Mit Analytics-Tracking
NavigationItem::create([
    'navigation_id' => $nav->id,
    'label' => 'Über uns',
    'route_name' => 'home',
    'anchor' => 'ueber',
    'data_attributes' => [
        'umami-event' => 'nav-click',
        'umami-event-target' => 'ueber',
    ],
    'order' => 4,
    'is_active' => true,
]);

// Verschachtelt (Dropdown)
$parent = NavigationItem::create([
    'navigation_id' => $nav->id,
    'label' => 'Mehr',
    'url' => '#',
    'order' => 5,
    'is_active' => true,
]);

NavigationItem::create([
    'navigation_id' => $nav->id,
    'parent_id' => $parent->id,
    'label' => 'Sub-Item',
    'url' => '/sub',
    'order' => 1,
    'is_active' => true,
]);
```

## Filament Admin

Navigationen werden über das Filament Admin Panel verwaltet:
`/admin/navigations`

Features:
- Drag & Drop Sortierung
- Inline-Bearbeitung aller Felder
- Repeater für Navigation Items
- Key-Value Editor für Route-Parameter und Data-Attribute
- Type-Filter und Suche

## MCP Tools (AI Integration)

Das System bietet 5 MCP-Tools für AI-gestützte Verwaltung:

### list-navigations
Listet alle Navigationen mit Items auf.

```json
{
  "navigations": [
    {
      "id": "uuid",
      "name": "Hauptnavigation",
      "type": "header",
      "is_active": true,
      "items_count": 4,
      "items": [...]
    }
  ]
}
```

### get-navigation
Holt eine spezifische Navigation mit allen Details.

```json
{
  "navigation_id": "uuid"
}
```

### update-navigation
Ersetzt alle Items einer Navigation.

```json
{
  "navigation_id": "uuid",
  "items": [
    {
      "label": "Home",
      "route_name": "home",
      "is_active": true
    },
    {
      "label": "FAQ",
      "route_name": "home",
      "anchor": "faq",
      "data_attributes": {
        "umami-event": "nav-click"
      },
      "is_active": true
    }
  ]
}
```

### create-navigation-item
Fügt ein neues Item zu einer Navigation hinzu.

```json
{
  "navigation_id": "uuid",
  "label": "Neuer Link",
  "route_name": "home",
  "anchor": "section",
  "is_active": true
}
```

### reorder-navigation-items
Sortiert Items neu.

```json
{
  "navigation_id": "uuid",
  "item_ids": ["uuid-3", "uuid-1", "uuid-2"]
}
```

## Seeder

Migriert die bestehende hardcodierte Navigation:

```bash
php artisan db:seed --class=NavigationSeeder
```

Erstellt:
- Header Navigation (4 Items)
- Footer Navigation (5 Items)
- Legal Navigation (2 Items)

## Factory Usage

```php
use App\Models\Navigation;
use App\Models\NavigationItem;

// Navigationen erstellen
$headerNav = Navigation::factory()->header()->create();
$footerNav = Navigation::factory()->footer()->create();
$inactiveNav = Navigation::factory()->inactive()->create();

// Items erstellen
$item = NavigationItem::factory()
    ->forNavigation($headerNav)
    ->withRoute('home')
    ->withAnchor('faq')
    ->withAnalytics('nav-click', 'faq')
    ->atOrder(1)
    ->create();

// Mit Parent
$parent = NavigationItem::factory()
    ->forNavigation($headerNav)
    ->create();

$child = NavigationItem::factory()
    ->withParent($parent)
    ->create();
```

## Testing

```bash
# Alle Navigation-Tests
php artisan test --filter=Navigation

# Nur Unit-Tests
php artisan test tests/Unit/Models/NavigationTest.php

# Nur Filament-Tests
php artisan test tests/Feature/Filament/Resources/NavigationResourceTest.php
```

## Besonderheiten

### Computed URL
Der `computed_url` Accessor erstellt die finale URL:
1. Prüft `route_name` → generiert Route mit `route_params`
2. Fallback zu `url` wenn keine Route
3. Hängt `anchor` mit `#` an

```php
$item->computed_url; // z.B. "https://example.com/home#faq"
```

### Data Attributes String
Der `data_attributes_string` Accessor konvertiert das Array zu HTML:

```php
$item->data_attributes = [
    'umami-event' => 'nav-click',
    'umami-event-target' => 'home'
];

$item->data_attributes_string;
// 'data-umami-event="nav-click" data-umami-event-target="home"'
```

### Cache Invalidierung
Navigations und Items nutzen das `ClearsResponseCache` Trait.
Bei jeder Änderung wird automatisch der Response-Cache geleert.

### View Composer
`AppServiceProvider` injiziert Navigationen in alle `layouts.app` Views:
- `$headerNavigation`
- `$footerNavigation`
- `$legalNavigation`

## Migration von hardcodierter Navigation

Die ursprüngliche Navigation war hardcodiert in `layouts/app.blade.php`.
Nach dem Seeder-Lauf ist sie vollständig datenbankbasiert.

Vergleich:
```blade
{{-- Alt: Hardcodiert --}}
<a href="{{ route('home') }}#ueber"
   data-umami-event="nav-click"
   data-umami-event-target="ueber">
  Über
</a>

{{-- Neu: Dynamisch --}}
@foreach ($headerNavigation->activeItems()->rootItems()->get() as $item)
  <a href="{{ $item->computed_url }}"
     {!! $item->data_attributes_string !!}>
    {{ $item->label }}
  </a>
@endforeach
```

## Best Practices

1. **Immer `computed_url` verwenden** statt direkt `url` oder `route_name`
2. **Anchor ohne `#` speichern** (wird automatisch hinzugefügt)
3. **Data-Attribute als Array** für einfache Verwaltung
4. **Active-Items filtern** für Performance
5. **Eager Loading** bei Navigation-Queries: `->with('items')`
6. **Order-Werte** mit Abstand (10, 20, 30) für späteres Einfügen

## Troubleshooting

### Navigation wird nicht angezeigt
- Prüfen ob `is_active = true` auf Navigation UND Items
- View Composer prüfen (Navigation muss geladen werden)
- Cache leeren: `php artisan responsecache:clear`

### Computed URL ist falsch
- Route existiert? `php artisan route:list`
- Route-Parameter korrekt? Müssen mit Route-Definition übereinstimmen
- Bei Fehlern fällt System auf `url` zurück

### Items nicht sortiert
- `order` Spalte prüfen
- Relationship nutzt `->orderBy('order')`
- In Filament Repeater ist Sortierung automatisch

## Weiterführende Dokumentation

- [Spatie Laravel Navigation](https://github.com/spatie/laravel-navigation)
- [Filament Forms](https://filamentphp.com/docs/forms)
- [Laravel MCP](https://laravel.com/docs/mcp)
