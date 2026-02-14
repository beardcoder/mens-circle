# Claude Skills für TYPO3 Development

Dieses Projekt enthält spezialisierte Claude Skills für TYPO3 v14.1 Entwicklung. Die Skills wurden basierend auf dem [redpop/claude-code-toolkit](https://github.com/redpop/claude-code-toolkit/tree/d12a0210ee749badea91a71ebafda4982911986f/docs/agents/typo3) erstellt und auf das mens-circle Projekt angepasst.

## Verfügbare Skills

### TYPO3-spezifische Skills

#### typo3-architect
System-Architektur und Enterprise-Level TYPO3 Implementierungen.

**Aktiviert bei:**
- Site-Architektur Design
- Multi-Site Setups
- Performance-Optimierung
- Content-Strategie
- Security-Architektur

**Expertise:**
- Multi-Site und Multi-Domain Konfigurationen
- Performance-Optimierung (Caching, CDN, Redis)
- Extension-Architektur
- Content-Modeling
- Sicherheits-Best-Practices

#### typo3-content-blocks
Flexible Content-Elemente mit TYPO3 Core-nahem Ansatz.

**Aktiviert bei:**
- Content-Element Entwicklung
- FlexForm Konfiguration
- TCA Customization
- Template-Integration

**Expertise:**
- Core-native Content-Elemente (nur Standard `tt_content` Felder)
- FlexForm XML Konfiguration
- DataProcessor Integration
- Responsive Template Design

#### typo3-extension-dev
TYPO3 Extension-Entwicklung mit Extbase und TYPO3 APIs.

**Aktiviert bei:**
- Extension-Erstellung
- Domain-Model Entwicklung
- Repository Pattern
- Backend-Module
- CLI Commands

**Expertise:**
- Moderne Extension-Struktur (TYPO3 v14)
- Extbase Domain-Models und Repositories
- TCA Konfiguration
- Dependency Injection (Autowiring)
- TYPO3 Console Commands

#### typo3-fluid
Fluid Templating und Frontend-Optimierung.

**Aktiviert bei:**
- Fluid Template-Entwicklung
- ViewHelper-Erstellung
- Template-Performance
- Responsive Design
- Asset-Management

**Expertise:**
- Advanced Fluid Template Patterns
- Custom ViewHelper Development
- Layout und Partial Management
- Hotwire Turbo Integration
- Accessibility (WCAG)

#### typo3-typoscript
TypoScript Konfiguration und Site-Setup.

**Aktiviert bei:**
- TypoScript-Konfiguration
- Site-Config (config.yaml)
- Routing Setup
- Caching-Strategien
- Troubleshooting

**Expertise:**
- Site Sets (TYPO3 v14)
- TypoScript PAGE Objekte
- Route Enhancers
- Conditions und Constants
- Performance-Tuning

### Weitere Skills

#### pest-testing
PHP Testing mit Pest 4 Framework.

**Aktiviert bei:** Tests schreiben, TDD, Assertions, Coverage

#### php-modernizer
PHP 8.4+ Modernisierung mit Framework-spezifischen Optimierungen.

**Aktiviert bei:** Code-Modernisierung, Refactoring, Simplification

Unterstützt TYPO3-spezifische Patterns wie:
- Extbase Optimierungen
- Repository Pattern Verbesserungen
- DataProcessor Vereinfachungen

#### tailwindcss-development
Tailwind CSS v4 Styling.

**Aktiviert bei:** CSS/Styling, UI-Änderungen, Responsive Design

## Skill Aktivierung

Die Skills werden **automatisch aktiviert**, wenn Claude Code relevante Keywords oder Aufgaben erkennt. Manuelle Aktivierung ist normalerweise nicht nötig.

### Manuelle Aktivierung (falls gewünscht)

In Claude Code können Skills auch manuell aktiviert werden:

```
@typo3-architect Design a multi-site architecture
@typo3-fluid Create a responsive event list template
@typo3-extension-dev Add a new domain model for testimonials
```

## Projekt-Kontext

Alle Skills sind auf das mens-circle Projekt zugeschnitten:

**Extension:** `packages/mens_circle`
**TYPO3 Version:** 14.1
**PHP Version:** 8.5
**Build Tool:** Bun + Vite

**Wichtige Pfade:**
- Extension: `packages/mens_circle/`
- Site Config: `config/sites/mens-circle/config.yaml`
- Site Set: `packages/mens_circle/Configuration/Sets/MensCircle/`

**Build Commands:**
- `ddev exec vendor/bin/typo3 cache:flush` - Cache leeren
- `bun run build` - Frontend Build
- `bun run dev` - Frontend Dev-Server

## Skill-Struktur

Jeder Skill enthält:

```
skill-name/
  SKILL.md          # Skill-Definition und Dokumentation
```

Die `SKILL.md` Datei hat folgenden Aufbau:
- YAML Frontmatter (Name, Description)
- "When to Apply" Section
- "Core Expertise Areas"
- "Project Context" (mens-circle spezifisch)
- "Best Practices"
- "Common Tasks"
- "Related Skills"

## Integration in das Projekt

Die Skills sind in `.claude/skills/` gespeichert und werden automatisch von Claude Code erkannt.

Die Konfiguration in `.claude/settings.local.json` aktiviert die Skills für das Projekt.

## Weiterführende Links

- [TYPO3 v14 Documentation](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/)
- [Extbase & Fluid Documentation](https://docs.typo3.org/m/typo3/book-extbasefluid/main/en-us/)
- [TypoScript Reference](https://docs.typo3.org/m/typo3/reference-typoscript/main/en-us/)
- [Original Toolkit (redpop)](https://github.com/redpop/claude-code-toolkit)

## Lizenz

Die Skills basieren auf dem [redpop/claude-code-toolkit](https://github.com/redpop/claude-code-toolkit) und sind angepasst für das mens-circle Projekt.
