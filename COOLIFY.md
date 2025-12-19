# Coolify v4 Deployment Guide

Diese Anleitung beschreibt das Deployment der Männerkreis Straubing Website auf Coolify v4.

## Voraussetzungen

-   Coolify v4 installiert und konfiguriert
-   Git Repository (GitHub, GitLab, etc.)
-   Domain konfiguriert und DNS auf Coolify Server zeigend

## Deployment-Schritte

### 1. Neues Projekt erstellen

1. In Coolify einloggen
2. Neues Projekt erstellen oder bestehendes auswählen
3. "Add New Resource" → "Application"

### 2. Repository verbinden

1. **Source**: Git Repository auswählen (GitHub/GitLab)
2. **Repository**: `mens-circle-design` auswählen
3. **Branch**: `main` (oder dein Production Branch)
4. **Build Pack**: `Dockerfile`

### 3. Build-Konfiguration

Coolify erkennt automatisch das `Dockerfile`. Folgende Einstellungen prüfen:

-   **Dockerfile Location**: `./Dockerfile`
-   **Docker Context**: `.`

### 4. Umgebungsvariablen setzen

In Coolify unter "Environment Variables" folgende Variablen hinzufügen:

```env
# App Settings
APP_NAME="Männerkreis Straubing"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:DEIN_APP_KEY_HIER
APP_URL=https://deine-domain.de

# Database (SQLite - im Container)
DB_CONNECTION=sqlite

# Cache & Sessions
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Mail (SMTP konfigurieren)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=dein-username
MAIL_PASSWORD=dein-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hallo@deine-domain.de"
MAIL_FROM_NAME="${APP_NAME}"

# FrankenPHP
OCTANE_SERVER=frankenphp
OCTANE_HTTPS=true
FRANKENPHP_WORKERS=auto

# Migration beim Start
MIGRATE_ON_START=true
```

### 5. APP_KEY generieren

Falls du noch keinen APP_KEY hast, generiere einen lokal:

```bash
php artisan key:generate --show
```

Kopiere den generierten Key in Coolify.

### 6. Volumes konfigurieren

Für persistente Daten in Coolify unter "Storages" hinzufügen:

| Mount Path      | Description                            |
| --------------- | -------------------------------------- |
| `/app/storage`  | Laravel Storage (Uploads, Logs, Cache) |
| `/app/database` | SQLite Datenbank                       |

### 7. Netzwerk & Ports

-   **Port**: `80` (HTTP intern)
-   Coolify übernimmt SSL/HTTPS automatisch via Traefik

### 8. Domain konfigurieren

Unter "Domains":

1. Domain hinzufügen: `deine-domain.de`
2. Optional: `www.deine-domain.de`
3. HTTPS aktivieren (Let's Encrypt)

### 9. Health Check

Der Container hat einen integrierten Health Check auf `/up`. Coolify nutzt diesen automatisch.

## Deployment starten

1. "Deploy" klicken
2. Build-Logs überwachen
3. Nach erfolgreichem Deployment die Domain aufrufen

## Troubleshooting

### Container startet nicht

1. Logs in Coolify prüfen
2. Sicherstellen, dass APP_KEY gesetzt ist
3. Prüfen ob alle erforderlichen ENV-Variablen gesetzt sind

### Datenbankfehler

```bash
# In Coolify Terminal/Console:
php artisan migrate:status
php artisan migrate --force
```

### Cache-Probleme

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Permissions

Die Permissions werden automatisch im Entrypoint gesetzt. Falls Probleme:

```bash
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache
```

## Updates deployen

1. Code in Git Repository pushen
2. In Coolify: "Redeploy" oder Webhook konfigurieren für automatisches Deployment

## Performance-Tuning

### FrankenPHP Workers

Anzahl der Worker anpassen:

```env
FRANKENPHP_WORKERS=4  # oder "auto" für CPU-Cores
```

### OPcache Preloading (Optional)

Für zusätzliche Performance kann OPcache Preloading aktiviert werden (siehe `docker/php/opcache.ini`).

## Backups

Wichtige Verzeichnisse für Backups:

-   `/app/database/database.sqlite` - Die SQLite-Datenbank
-   `/app/storage/app` - Hochgeladene Dateien

In Coolify können Backups unter "Backups" konfiguriert werden.
