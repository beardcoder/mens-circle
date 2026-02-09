# Männerkreis Niederbayern / Straubing (TYPO3 v14.1)

Kompletter Neubau auf **TYPO3 Core 14.1** mit Fokus auf:

- einfache, wartbare Architektur (KISS)
- hohe Performance durch Core-Caching + minimales Frontend
- core-nahe Umsetzung über Site Set, Extbase und TCA

## Stack

- TYPO3 CMS 14.1 (Composer Setup)
- PHP 8.2+
- Core-Module: Extbase, Fluid, Form, SEO
- Eigenes Sitepackage: `markussommer/mens-circle-sitepackage` (`EXT:mens_circle`)

## Enthaltene Funktionen

- Eventverwaltung mit Slug, Publikationsstatus, Kapazität, iCal-Export
- Event-Anmeldung mit Teilnehmerdaten und optionalem Newsletter-Opt-in
- Newsletter-Anmeldung + Token-basierte Abmeldung
- Testimonial-Einreichung und veröffentlichte Testimonials
- Site Set + Routen-Enhancer für saubere URLs

## Projektstruktur

- `packages/mens_circle` - Sitepackage + Domainlogik
- `config/sites/mens-circle/config.yaml` - Site-Konfiguration und Route-Enhancer
- `config/system/additional.php` - sichere Basis-Konfiguration

## Setup

1. Abhängigkeiten installieren:

```bash
composer install
```

2. TYPO3 Grundinstallation durchführen:

```bash
vendor/bin/typo3 setup
```

3. Frontend Assets bauen (Vite):

```bash
bun install
bun run build
```

4. In TYPO3 Backend:

```text
- Site Set "Männerkreis Site Set" aktivieren
- Extension "mens_circle" aktivieren
- DB Schema über Wartung > Analyze Database Structure ausführen
- Seitenbaum anlegen (z. B. Start, Event, Newsletter, Testimonials, Impressum, Datenschutz)
- Plugins auf die passenden Seiten setzen
```

## Wichtige Sicherheitshinweise

- Keine Zugangsdaten im Repository speichern.
- SMTP und DB nur über lokale/Server-Konfiguration einrichten.
- Regelmäßige Backups von Datenbank und `fileadmin/` einplanen.
- Produktiv: `trustedHostsPattern` und `base` auf echte Domain einschränken.

## Performance-Hinweise

- Nicht schreibende Seiten/Plugins bleiben cachebar.
- Nur Submit-Actions sind non-cacheable.
- CSS/JS werden über Vite gebaut und als statische Dateien aus `EXT:mens_circle/Resources/Public/Build` ausgeliefert.
