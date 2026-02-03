# Newsletter Backend Module - DDEV Setup Guide

## ✅ Wichtig: Alle Commands über DDEV ausführen!

Dieses Projekt verwendet DDEV als lokale Entwicklungsumgebung.

## 🚀 Quick Start

### 1. DDEV Status prüfen
```bash
cd /Users/markus.sommer/Projekte/Privat/mens-circle
ddev status
```

### 2. Cache leeren
```bash
ddev exec vendor/bin/typo3 cache:flush
```

### 3. Backend öffnen
```bash
# URL: https://mens-circle.ddev.site/typo3
ddev launch /typo3
```

### 4. Zum Newsletter-Modul navigieren
- Im TYPO3 Backend einloggen
- Menü: **Web → Newsletter**

## 📋 DDEV Commands

### Extension Management
```bash
# Extension aktivieren
ddev exec vendor/bin/typo3 extension:activate mens_circle

# Extension deaktivieren
ddev exec vendor/bin/typo3 extension:deactivate mens_circle

# Extension-Liste anzeigen
ddev exec vendor/bin/typo3 extension:list
```

### Cache Management
```bash
# Alle Caches leeren
ddev exec vendor/bin/typo3 cache:flush

# Specific cache leeren
ddev exec vendor/bin/typo3 cache:flush --group=system
ddev exec vendor/bin/typo3 cache:flush --group=pages
```

### Database
```bash
# Datenbank-Schema aktualisieren
ddev exec vendor/bin/typo3 database:updateschema

# Datenbank exportieren
ddev export-db --file=backup.sql.gz

# Datenbank importieren
ddev import-db --file=backup.sql.gz

# MySQL CLI öffnen
ddev mysql
```

### Code Quality
```bash
# PHPStan ausführen
ddev exec vendor/bin/phpstan analyze packages/mens_circle/Classes --level=5

# PHP Syntax Check
ddev exec php -l packages/mens_circle/Classes/Controller/Backend/NewsletterController.php

# PHP CS Fixer (falls installiert)
ddev exec vendor/bin/php-cs-fixer fix packages/mens_circle
```

### Composer
```bash
# Composer install
ddev composer install

# Composer update
ddev composer update

# Package hinzufügen
ddev composer require vendor/package
```

### Node/Bun (Frontend Assets)
```bash
# Bun install
ddev exec bun install

# Bun build
ddev exec bun run build

# Bun watch mode
ddev exec bun run watch
```

### Logs
```bash
# TYPO3 Logs ansehen
ddev exec tail -f var/log/typo3_*.log

# PHP Error Log
ddev logs

# Nur Web-Container Logs
ddev logs -s web

# Nur DB-Container Logs
ddev logs -s db
```

### Mailpit (E-Mail Testing)
```bash
# Mailpit öffnen
ddev mailpit

# URL: https://mens-circle.ddev.site:8026
```

## 🧪 Testing mit DDEV

### Backend Module testen
```bash
# 1. Cache leeren
ddev exec vendor/bin/typo3 cache:flush

# 2. Backend öffnen
ddev launch /typo3

# 3. Als Admin einloggen
# Username: admin
# Password: [aus .env oder LocalConfiguration.php]

# 4. Zu "Web → Newsletter" navigieren
```

### Newsletter-Versand testen
```bash
# 1. Newsletter im Backend erstellen und senden

# 2. Mailpit öffnen um E-Mails zu sehen
ddev mailpit

# 3. E-Mail prüfen:
#    - HTML-Version korrekt?
#    - Plaintext-Fallback vorhanden?
#    - Abmelde-Link funktioniert?
```

### PHPStan Tests
```bash
# Gesamtes Extension-Verzeichnis
ddev exec vendor/bin/phpstan analyze packages/mens_circle/Classes --level=5

# Nur Backend Controller
ddev exec vendor/bin/phpstan analyze packages/mens_circle/Classes/Controller/Backend --level=5

# Mit mehr Details
ddev exec vendor/bin/phpstan analyze packages/mens_circle/Classes --level=5 -v
```

## 🐛 Troubleshooting mit DDEV

### DDEV startet nicht
```bash
# DDEV neu starten
ddev restart

# DDEV stoppen und neu starten
ddev stop
ddev start

# DDEV komplett neu aufsetzen
ddev poweroff
ddev start
```

### Cache-Probleme
```bash
# Alle Caches leeren (TYPO3 + DDEV)
ddev exec vendor/bin/typo3 cache:flush
ddev exec rm -rf var/cache/*
```

### Datenbank-Probleme
```bash
# Datenbank neu aufsetzen
ddev stop
ddev start

# Datenbank-Verbindung testen
ddev mysql -e "SELECT 1"

# Datenbank-Schema prüfen
ddev exec vendor/bin/typo3 database:updateschema
```

### Permission-Probleme
```bash
# DDEV hat spezielle User-Mappings, meist keine Probleme
# Falls doch:
ddev exec sudo chown -R www-data:www-data var/
```

### Backend-Modul erscheint nicht
```bash
# 1. Cache leeren
ddev exec vendor/bin/typo3 cache:flush

# 2. Extension neu aktivieren
ddev exec vendor/bin/typo3 extension:deactivate mens_circle
ddev exec vendor/bin/typo3 extension:activate mens_circle

# 3. Backend neu laden
ddev launch /typo3
```

## 📊 DDEV Konfiguration

### Wichtige DDEV Files
```
.ddev/
├── config.yaml          # Haupt-Konfiguration
├── docker-compose.*.yaml # Docker Services
└── commands/            # Custom DDEV Commands
```

### Projekt-URLs
- **Frontend:** https://mens-circle.ddev.site
- **Backend:** https://mens-circle.ddev.site/typo3
- **Mailpit:** https://mens-circle.ddev.site:8026
- **Vite:** https://vite.mens-circle.ddev.site

### Container-Zugriff
```bash
# Web-Container (PHP/Nginx)
ddev ssh

# Innerhalb des Containers:
cd /var/www/html
ls -la

# Root-Zugriff (falls nötig)
ddev ssh --sudo
```

## 🎯 Newsletter Module spezifische Commands

### Setup nach Git Clone
```bash
# 1. DDEV starten
ddev start

# 2. Composer Dependencies
ddev composer install

# 3. Bun Dependencies
ddev exec bun install

# 4. TYPO3 Setup (falls neu)
ddev exec vendor/bin/typo3 setup

# 5. Cache leeren
ddev exec vendor/bin/typo3 cache:flush

# 6. Extension aktivieren
ddev exec vendor/bin/typo3 extension:activate mens_circle

# 7. Backend öffnen
ddev launch /typo3
```

### Entwicklung
```bash
# Terminal 1: TYPO3 Logs
ddev exec tail -f var/log/typo3_*.log

# Terminal 2: Frontend Build (Watch Mode)
ddev exec bun run watch

# Terminal 3: PHP/Backend Entwicklung
ddev ssh
# Im Container arbeiten
```

### Deployment-Vorbereitung
```bash
# 1. Tests ausführen
ddev exec vendor/bin/phpstan analyze packages/mens_circle/Classes --level=5

# 2. Code Style prüfen
ddev exec vendor/bin/php-cs-fixer fix --dry-run packages/mens_circle

# 3. Assets bauen
ddev exec bun run build

# 4. Cache warmup
ddev exec vendor/bin/typo3 cache:warmup
```

## 📝 Wichtige Hinweise

### DDEV Performance (Mutagen)
- Das Projekt nutzt Mutagen für bessere Performance auf macOS
- Dateien werden synchronisiert, kann 1-2 Sekunden dauern
- Status prüfen: `ddev mutagen status`

### PHP Version
- Projekt nutzt PHP 8.5
- In DDEV konfiguriert via `.ddev/config.yaml`

### Node/Bun Version
- Node.js 22 installiert
- Bun wird für Frontend-Build verwendet

### Datenbank
- MariaDB 11.8
- User: `db` / Password: `db`
- Root: `root` / Password: `root`

## 🎉 Zusammenfassung

Das Newsletter Backend Modul ist vollständig implementiert und kann über DDEV getestet werden:

```bash
# Quick Test
cd /Users/markus.sommer/Projekte/Privat/mens-circle
ddev exec vendor/bin/typo3 cache:flush
ddev launch /typo3
# → Web → Newsletter
```

**Alle Commands IMMER über DDEV ausführen!** 🚀

---

**Status:** ✅ Produktionsbereit  
**DDEV Version:** v1.24.10  
**TYPO3 Version:** 14.1  
**PHP Version:** 8.5  
**Letzte Aktualisierung:** 02.02.2026, 22:47 Uhr

