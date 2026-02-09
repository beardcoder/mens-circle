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

## Asynchrone Benachrichtigungen (Messenger)

- Newsletter, Event-Bestaetigung und Event-Reminder werden an den TYPO3 Message Bus uebergeben.
- Routing ist auf `doctrine` gesetzt (Queue in DB), daher muss der Worker laufen.

```bash
vendor/bin/typo3 messenger:consume doctrine
```

Reminder-Dispatch (nur Queueing, Versand erfolgt im Worker):

```bash
vendor/bin/typo3 menscircle:events:dispatch-reminders --hours-before=24 --window-minutes=120
```

Dry-Run:

```bash
vendor/bin/typo3 menscircle:events:dispatch-reminders --dry-run
```

## SMS Konfiguration

- SMS Versand ist optional und laeuft asynchron ueber den Message Bus.
- Konfiguration in Extension Settings (`ext_conf_template.txt`) oder per Umgebungsvariable:

```bash
export MENSCIRCLE_SMS_API_KEY="dein-key"
```

## Prompt: TYPO3 Core-nahe Icon-Erstellung

```markdown
Du bist Senior Product Icon Designer mit Fokus auf TYPO3 Backend-Icons.

Ziel:
Erstelle ein konsistentes SVG-Iconset fuer EXT:mens_circle, das stilistisch zu TYPO3 Core Icons passt:
- klar, reduziert, technisch sauber
- 1-farbige Vektoricons
- 16x16 Grid als Primarrahmen
- auch in 32x32 sauber skalierend
- keine Farbverlaeufe, keine Schatten, keine 3D-Effekte
- Strichstaerke optisch konsistent (ca. 1.5-1.75px bei 16x16)
- runde Kappen/Ecken nur wenn sinnvoll

Visueller Stil:
- geometrisch, ruhige Formen, gute Lesbarkeit bei kleiner Groesse
- positive/negative Flaechen klar trennen
- typo3-core-aehnliche Semantik statt illustrativer Details

Kontext:
Die Icons werden in TYPO3 fuer Module, Content-Elemente, Plugins und Datensaetze genutzt.
Dateien muessen direkt als `SvgIconProvider` funktionieren.

Erzeuge Icons fuer:
1) Backend Module
- events
- newsletter

2) Content Elemente
- hero
- intro
- text-section
- value-items
- moderator
- journey-steps
- testimonials
- faq
- newsletter
- cta
- whatsapp

3) Plugins
- event
- newsletter
- testimonial

4) Domain Datensaetze
- event
- participant
- registration
- newsletter-subscription
- testimonial

Technische Vorgaben:
- reines SVG, kein eingebettetes Rasterbild
- `viewBox="0 0 16 16"` bevorzugt
- moeglichst wenige Pfade, optimiert fuer Dateigroesse
- konsistente Benennung nach Dateiname
- Icons muessen auch invertiert (dark/light backend) lesbar bleiben
- keine Inline-Styles, nur saubere SVG-Attribute

Output-Format:
1) Kurze Design-System-Zusammenfassung (5-8 Punkte)
2) Fuer jedes Icon:
   - Dateiname
   - semantische Begruendung in 1 Satz
   - komplettes SVG in einem eigenen Codeblock
3) Zum Schluss:
   - kurze QA-Checkliste fuer TYPO3 (Groesse, Kontrast, Lesbarkeit, Konsistenz)
```
