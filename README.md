# Männerkreis Niederbayern

Event Management und Community-Plattform für Männerkreis-Veranstaltungen in Niederbayern und Straubing.

## Tech-Stack

- **TYPO3** v14.1
- **PHP** 8.3+
- **Extbase** — Domain Models, Repositories, Controllers
- **Fluid 5** — Templates mit PAGEVIEW
- **Site Sets** — Konfiguration über `config.yaml` / `settings.yaml`
- **PSR-14 Events** — Asynchrone Workflows (E-Mail, SMS)

## Projektstruktur

```
├── config/
│   └── sites/
│       └── mens-circle/
│           └── config.yaml          # TYPO3 Site-Konfiguration
├── packages/
│   └── mens_circle/                 # Extension
│       ├── Classes/                 # PSR-4 Autoloading
│       │   ├── Controller/
│       │   ├── Domain/
│       │   │   ├── Model/
│       │   │   └── Repository/
│       │   ├── Event/               # PSR-14 Events
│       │   ├── EventListener/
│       │   ├── Service/
│       │   ├── Utility/
│       │   └── ViewHelpers/
│       ├── Configuration/
│       │   ├── Sets/MensCircle/     # Site Sets (TYPO3 v14)
│       │   ├── TCA/                 # Table Configuration Array
│       │   └── Extbase/
│       ├── Resources/
│       │   ├── Private/             # Templates, Partials, Layouts, Language
│       │   └── Public/              # CSS, JS, Icons
│       └── Tests/
├── web/                             # TYPO3 Web Root (symlinks, generated)
├── composer.json
└── phpunit.xml
```

## Installation

```bash
# 1. Clone Repository
git clone https://github.com/beardcoder/mens-circle.git
cd mens-circle

# 2. Composer Install
composer install

# 3. TYPO3 Initial Setup
# Öffne im Browser: http://localhost/typo3/install.php
# Folge dem Installationsassistenten

# 4. Extension aktivieren
# Im TYPO3 Backend: Extension Manager → mens_circle aktivieren
# oder: php vendor/bin/typo3 extension:activate mens_circle
```

## Development

```bash
# Unit Tests ausführen
composer run test -- --testsuite=Unit

# Alle Tests
composer run test

# Code Formatting prüfen
composer run lint

# Code formatieren
composer run format
```

## Features

| Feature | Status |
|---------|--------|
| Event Management (CRUD, Bilder, Kapazität) | ✅ |
| Event Registration mit Validierung | ✅ |
| Bestätigungsmail per E-Mail | ✅ |
| SMS Benachrichtigung (Seven.io) | ✅ |
| Newsletter (Double-Opt-In) | ✅ |
| Testimonials (Einreichung + Freigabe) | ✅ |
| TYPO3 PAGEVIEW + Fluid 5 Templates | ✅ |
| SEO (Meta Tags, Schema.org) | ✅ |
| Responsive Design | ✅ |

## Konfiguration

Die Extension-Einstellungen werden über **Site Sets** verwaltet:

`packages/mens_circle/Configuration/Sets/MensCircle/settings.yaml`

Wichtige Einstellungen:
- `siteName` / `siteTagline` — Seitenüberschriften
- `eventPageId` / `footerPageId` — Page-IDs im Backend setzen
- `features.enableNewsletter` — Newsletter Ein-/Ausschalten
- `sms.apiKey` — Seven.io API-Schlüssel für SMS
- `email.fromEmail` — Absender-E-Mail-Adresse
