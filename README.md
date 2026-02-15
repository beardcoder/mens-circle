# Mens Circle Niederbayern / Straubing (TYPO3 v14.1)

Rebuild on **TYPO3 Core 14.1** with focus on:

- simple, maintainable architecture (KISS)
- high performance via core caching and minimal frontend payload
- core-oriented implementation with Site Set, Extbase, and TCA

## Stack

- TYPO3 CMS 14.1 (Composer setup)
- PHP 8.5
- Core modules: Extbase, Fluid, Form, SEO
- Sitepackage: `beardcoder/mens-circle-sitepackage` (`EXT:mens_circle`)
- Frontend build: Bun + Vite
- Image processing: GraphicsMagick with imagick PHP extension
- System locale: German (de_DE.UTF-8)

## Implemented Features

- Event management with slug, publication status, capacity, and iCal export
- Event registration with participant data and optional newsletter opt-in
- Newsletter signup with token-based unsubscribe
- Testimonial submission and published testimonials
- Site Set and route enhancers for clean URLs
- Async notifications via TYPO3 Messenger (newsletter, event confirmation, reminders)
- Optional async SMS delivery
- Custom backend modules (events, newsletter) and dashboard widgets
- Custom backend theme (`menscircle`) with website-aligned colors

## Project Structure

- `packages/mens_circle` - sitepackage and domain logic
- `config/sites/mens-circle/config.yaml` - site configuration and route enhancers
- `config/system/additional.php` - secure baseline configuration

## Setup

1. Install dependencies:

```bash
composer install
bun install
```

2. Run TYPO3 setup:

```bash
vendor/bin/typo3 setup
```

3. Build frontend assets:

```bash
bun run build
```

4. In TYPO3 backend:

```text
- Activate Site Set "Mens Circle Site Set"
- Activate extension "mens_circle"
- Run DB schema update in Maintenance > Analyze Database Structure
- Create page tree (e.g. Home, Event, Newsletter, Testimonials, Imprint, Privacy)
- Add plugins to their target pages
```

## Runtime Commands

Run messenger worker:

```bash
vendor/bin/typo3 messenger:consume doctrine
```

Dispatch event reminders (queue only):

```bash
vendor/bin/typo3 menscircle:events:dispatch-reminders --hours-before=24 --window-minutes=120
```

Dry run:

```bash
vendor/bin/typo3 menscircle:events:dispatch-reminders --dry-run
```

Import Laravel SQL dump:

```bash
ddev exec vendor/bin/typo3 menscircle:import:laravel-sql packages/mens_circle/live.sql --truncate -n
```

Flush caches:

```bash
vendor/bin/typo3 cache:flush
```

## Image Processing

The project uses GraphicsMagick for high-performance image processing with the imagick PHP extension.

**Verify installation:**
```bash
# Check GraphicsMagick
ddev exec gm version

# Check PHP extensions
ddev exec php -m | grep -E "(gd|exif|imagick|intl)"
```

For detailed information about GraphicsMagick and locale setup, see `GRAPHICS_LOCALE.md`.

## SMS Configuration

SMS is optional and processed asynchronously via TYPO3 Messenger.

Configure via extension settings or environment variable:

```bash
export MENSCIRCLE_SMS_API_KEY="your-key"
```

## Security Notes

- Do not store secrets in the repository.
- Provide SMTP/DB credentials via environment or server config.
- Keep regular backups for database and `fileadmin/`.
- Restrict `trustedHostsPattern` and site `base` on production.

## Localization

- Language files use **XLIFF 2.0**.
- Default source language is German (`*.xlf`).
- English translations are available in `*.en.xlf`.
- System locale is set to `de_DE.UTF-8` for proper character encoding and date/time formatting.
- See `GRAPHICS_LOCALE.md` for details on locale configuration.

## Prompt: Core-aligned TYPO3 Icon Creation

```markdown
You are a senior product icon designer focused on TYPO3 backend icon systems.

Goal:
Create a consistent SVG icon set for EXT:mens_circle that matches TYPO3 core icon language:
- clear, reduced, technically clean
- monochrome vector icons
- 16x16 as primary grid
- also crisp at 32x32
- no gradients, no shadows, no 3D effects
- consistent stroke weight (approx. 1.5-1.75px at 16x16)
- rounded caps/joins only when it improves readability

Visual style:
- geometric, calm forms, excellent legibility at small sizes
- clear separation of positive/negative space
- TYPO3-like semantics instead of illustrative details

Context:
Icons are used in TYPO3 for backend modules, content elements, plugins, and records.
Files must work directly with `SvgIconProvider`.

Create icons for:
1) Backend modules
- events
- newsletter

2) Content elements
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

4) Domain records
- event
- participant
- registration
- newsletter-subscription
- testimonial

Technical requirements:
- pure SVG, no embedded raster data
- prefer `viewBox=\"0 0 16 16\"`
- minimal path complexity, optimized file size
- consistent naming by filename
- readable in both dark/light backend contexts
- no inline CSS; use clean SVG attributes only

Output format:
1) Short design system summary (5-8 bullets)
2) For each icon:
   - filename
   - one-line semantic rationale
   - complete SVG in its own code block
3) Final:
   - short TYPO3 QA checklist (size, contrast, readability, consistency)
```
