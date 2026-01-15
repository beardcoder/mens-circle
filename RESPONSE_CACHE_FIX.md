# Response Cache Fix für Production (FrankenPHP + Octane)

## Problem
Der Response Cache wurde zwar generiert, aber nicht ausgeliefert, weil:

1. **Octane Cache Driver funktioniert nur mit Swoole, nicht mit FrankenPHP**
2. Der Response Cache Service musste in Octane's Flush-Array aufgenommen werden
3. Das Standard Cache-Profile war nicht optimal für Octane konfiguriert

## Implementierte Lösung

### 1. Octane Konfiguration (`config/octane.php`)
- Response Cache wurde zum `flush`-Array hinzugefügt, damit er bei jedem Request neu aufgelöst wird

### 2. Response Cache Konfiguration (`config/responsecache.php`)
- Cache-Driver auf `failover` geändert (nutzt Octane falls verfügbar, sonst File)
- Custom `OctaneCacheProfile` implementiert
- Debug-Header aktiviert für besseres Monitoring

### 3. Custom Cache Profile (`app/Support/ResponseCache/OctaneCacheProfile.php`)
- Schließt Admin/Filament Routen vom Caching aus
- Unterstützt user-spezifische Caches (falls nötig)
- Optimiert für Octane-Umgebungen

## Deployment auf Production

### Schritt 1: Code deployen
```bash
git pull origin main
```

### Schritt 2: Dependencies aktualisieren (falls nötig)
```bash
composer install --optimize-autoloader --no-dev
```

### Schritt 3: Cache leeren
```bash
php artisan responsecache:clear
php artisan cache:clear
php artisan config:clear
```

### Schritt 4: Config cachen
```bash
php artisan config:cache
```

### Schritt 5: Octane neu starten
```bash
php artisan octane:reload
# oder
systemctl restart frankenphp
# oder via Docker
docker-compose restart
```

### Schritt 6: Testen
```bash
# Ersten Request (sollte Cache generieren)
curl -I https://deine-domain.de/

# Zweiten Request (sollte aus Cache kommen)
curl -I https://deine-domain.de/
```

**Schaue in den Response-Headern nach:**
- `laravel-responsecache: <timestamp>` - Zeigt wann gecached wurde
- `laravel-responsecache-age: <seconds>` - Zeigt das Alter des Caches

## Environment Variables (Production .env)

Stelle sicher, dass diese Werte in deiner Production `.env` gesetzt sind:

```env
# Response Cache
RESPONSE_CACHE_ENABLED=true
RESPONSE_CACHE_LIFETIME=604800  # 7 Tage in Sekunden
RESPONSE_CACHE_DRIVER=failover  # Verwendet failover (octane -> file)
RESPONSE_CACHE_HEADER=true      # Debug-Header aktivieren

# Cache Store (für FrankenPHP muss es 'failover' oder 'file' sein, nicht 'octane')
CACHE_STORE=failover
```

## Monitoring

### Cache Status prüfen
```bash
# Alle Caches anzeigen
php artisan cache:table

# Response Cache löschen
php artisan responsecache:clear
```

### Response Header überprüfen
```bash
curl -I https://deine-domain.de/ | grep laravel-responsecache
```

## Wichtige Hinweise

1. **FrankenPHP + Octane Cache Driver**: Der `octane` Cache-Driver funktioniert NUR mit Swoole. Bei FrankenPHP muss `file` oder `failover` verwendet werden.

2. **Admin-Bereich**: Admin/Filament-Routen werden NICHT gecached (siehe `OctaneCacheProfile`)

3. **Personalisierte Inhalte**: Falls du user-spezifische Inhalte hast, wird automatisch ein Suffix mit der User-ID angehängt

4. **Debug-Modus**: Die Cache-Header sind aktiviert, um zu sehen ob der Cache funktioniert. In Production kannst du `RESPONSE_CACHE_HEADER=false` setzen.

## Troubleshooting

### Cache wird nicht verwendet?
1. Prüfe die Response-Header mit `curl -I`
2. Stelle sicher, dass `RESPONSE_CACHE_ENABLED=true` in der `.env`
3. Prüfe ob die Route ein GET-Request ist
4. Prüfe ob die Response erfolgreich ist (2xx Status)

### Cache wird nicht aktualisiert?
```bash
php artisan responsecache:clear
php artisan octane:reload
```

### File-Permissions?
```bash
chmod -R 775 storage/framework/cache
chown -R www-data:www-data storage/framework/cache
```
