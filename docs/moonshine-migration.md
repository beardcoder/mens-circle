# MoonShine Migration Documentation

## Übersicht

Dieses Dokument beschreibt die Integration von MoonShine als paralleles Admin-Panel neben Filament im mens-circle Projekt.

## Architektur

### Parallelbetrieb von Filament und MoonShine

- **Filament**: Läuft unter `/admin` (wie bisher)
- **MoonShine**: Läuft unter `/moonshine` (neu)
- Beide Systeme nutzen **separate Authentifizierung**
- Beide Systeme nutzen **dieselben Eloquent Models**
- Keine gegenseitigen Abhängigkeiten oder Konflikte

## Installation

### 1. Composer Dependencies

MoonShine wurde bereits zu `composer.json` hinzugefügt:

```bash
composer require moonshine/moonshine
```

### 2. MoonShine Installation

Nach der Installation der Dependencies, führe aus:

```bash
php artisan moonshine:install
```

Dieser Befehl:
- Publiziert MoonShine Konfigurationsdateien
- Erstellt notwendige Migrationsdateien
- Richtet die grundlegende Struktur ein

### 3. Datenbank Migration

Führe die MoonShine-spezifischen Migrationen aus:

```bash
php artisan migrate
```

Dies erstellt Tabellen für MoonShine-Benutzer und -Rollen (getrennt von den App-Benutzern).

### 4. Admin-Benutzer erstellen

Erstelle einen MoonShine-Admin-Benutzer:

```bash
php artisan moonshine:user
```

Alternativ kann dies über die Datenbank oder Seeds erfolgen.

## Konfiguration

### MoonShine Konfiguration (`config/moonshine.php`)

Die wichtigsten Konfigurationsparameter:

```php
'route' => [
    'prefix' => env('MOONSHINE_ROUTE_PREFIX', 'moonshine'),
],

'title' => 'Männerkreis Niederbayern - MoonShine',
'logo' => '/logo-color.svg',

'auth' => [
    'enable' => true,
    'guard' => 'moonshine',  // Separater Guard
],

'locale' => 'de',
```

### Environment Variables

Füge zu `.env` hinzu:

```env
MOONSHINE_ROUTE_PREFIX=moonshine
MOONSHINE_TITLE="Männerkreis Niederbayern - MoonShine"
MOONSHINE_LOGO=/logo-color.svg
```

### Service Provider Registration

Der `MoonShineServiceProvider` wurde in `app/Providers/MoonShineServiceProvider.php` erstellt und muss in `config/app.php` registriert werden (Laravel entdeckt ihn automatisch durch Package Discovery).

## Struktur

### Verzeichnisstruktur

```
app/
├── MoonShine/
│   ├── Pages/
│   │   └── Dashboard.php          # MoonShine Dashboard
│   └── Resources/
│       └── EventResource.php      # Beispiel-Resource für Events
├── Providers/
│   ├── Filament/
│   │   └── AdminPanelProvider.php # Filament bleibt unverändert
│   └── MoonShineServiceProvider.php # Neuer MoonShine Provider
config/
├── filament.php                    # Filament Config (unverändert)
└── moonshine.php                   # Neue MoonShine Config
```

### Resources

MoonShine Resources befinden sich in `app/MoonShine/Resources/` und folgen dem MoonShine-Pattern:

- `EventResource.php` - Beispiel-Resource für das Event-Model
- Weitere Resources können nach Bedarf hinzugefügt werden

## Verwendung

### Zugriff auf die Admin-Panels

Nach erfolgreicher Installation:

1. **Filament**: `http://localhost/admin`
   - Verwendet bestehende User-Authentifizierung
   - Unveränderte Funktionalität

2. **MoonShine**: `http://localhost/moonshine`
   - Separate MoonShine-User-Authentifizierung
   - Neue UI mit MoonShine-Komponenten

### Development Server starten

```bash
# Mit dem bestehenden dev-Script
composer run dev

# Oder manuell
php artisan serve
```

Beide Panels sind dann erreichbar:
- Filament: http://127.0.0.1:8000/admin
- MoonShine: http://127.0.0.1:8000/moonshine

## Testing

### Manuelle Tests

1. **Filament funktioniert weiterhin**:
   - Login unter `/admin`
   - Alle bestehenden Features testen
   - Events, Registrations, etc. verwalten

2. **MoonShine ist erreichbar**:
   - Login unter `/moonshine`
   - Dashboard anzeigen
   - Event-Resource testen
   - CRUD-Operationen durchführen

3. **Keine Konflikte**:
   - Beide Panels parallel verwenden
   - Datenänderungen in einem Panel sind im anderen sofort sichtbar
   - Keine Authentifizierungs-Konflikte

### Automated Tests

Falls vorhanden, sollten folgende Tests erstellt/angepasst werden:

```php
// Test: Filament läuft weiter
public function test_filament_panel_is_accessible()
{
    $response = $this->get('/admin');
    $response->assertStatus(200);
}

// Test: MoonShine ist erreichbar
public function test_moonshine_panel_is_accessible()
{
    $response = $this->get('/moonshine');
    $response->assertStatus(200);
}
```

## Wichtige Hinweise

### Separate Authentifizierung

MoonShine und Filament verwenden **separate Guards und Benutzer-Tabellen**:

- Filament: `users` Tabelle (bestehend)
- MoonShine: `moonshine_users` Tabelle (neu)

Dies verhindert Konflikte und ermöglicht unabhängige Benutzer-Verwaltung.

### Models Sharing

Beide Systeme verwenden **dieselben Eloquent Models** aus `app/Models/`:

- Event
- Registration
- Participant
- etc.

Änderungen an Models wirken sich auf beide Panels aus.

### Keine Breaking Changes

Die Integration hat **keine Breaking Changes** für Filament:

- Alle Filament-Routes bleiben unverändert
- Filament-Konfiguration bleibt unverändert
- Bestehende Filament-Resources bleiben unverändert
- Filament-Authentifizierung bleibt unverändert

## Nächste Schritte

### Weitere Resources erstellen

Erstelle weitere MoonShine-Resources nach Bedarf:

```bash
php artisan moonshine:resource ParticipantResource
php artisan moonshine:resource RegistrationResource
```

### Anpassungen

- **Branding**: Logo und Farben in `config/moonshine.php`
- **Menu**: Menü-Struktur in `MoonShineServiceProvider::menu()`
- **Dashboard**: Widgets in `app/MoonShine/Pages/Dashboard.php`
- **Permissions**: Rollen und Berechtigungen über MoonShine-UI

### Migration weg von Filament (optional, später)

Falls später eine vollständige Migration gewünscht ist:

1. Alle Filament-Resources in MoonShine portieren
2. Benutzer migrieren oder synchronisieren
3. Filament-Package entfernen
4. Route von `/admin` auf MoonShine umleiten

Dies ist jedoch **nicht Teil dieser Integration** - der aktuelle Fokus liegt auf **Parallelbetrieb**.

## Troubleshooting

### MoonShine-Seite zeigt 404

Stelle sicher, dass:
- `php artisan moonshine:install` ausgeführt wurde
- Routes gecacht wurden: `php artisan route:clear`
- Service Provider registriert ist

### Authentifizierungs-Probleme

- Prüfe `.env` für richtige Guard-Konfiguration
- Führe `php artisan config:clear` aus
- Erstelle Admin-User: `php artisan moonshine:user`

### Konflikte zwischen Panels

Falls unerwartete Konflikte auftreten:
- Stelle sicher, dass unterschiedliche Guards verwendet werden
- Prüfe, ob Session-Namen kollidieren
- Cache leeren: `php artisan cache:clear`

## Support

Bei Fragen oder Problemen:

- MoonShine Dokumentation: https://moonshine-laravel.com/docs
- Filament Dokumentation: https://filamentphp.com/docs
- Laravel Dokumentation: https://laravel.com/docs

## Changelog

### 2026-01-19 - Initial MoonShine Integration

- ✅ MoonShine Package zu composer.json hinzugefügt
- ✅ MoonShine Konfiguration erstellt
- ✅ MoonShineServiceProvider implementiert
- ✅ Example EventResource erstellt
- ✅ Dashboard Page erstellt
- ✅ Dokumentation erstellt
- ⏳ Composer Install ausstehend (GitHub Auth Issue)
- ⏳ Migration & Admin-User-Erstellung ausstehend
- ⏳ Testing ausstehend
