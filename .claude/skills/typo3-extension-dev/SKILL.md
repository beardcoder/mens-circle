---
name: typo3-extension-dev
description: >
  Develop TYPO3 v14 extensions with Claude Code. Use this skill whenever the user mentions TYPO3 extensions,
  TYPO3 sitepackages, TYPO3 plugins, TypoScript, Fluid templates, Extbase controllers, TCA configuration,
  backend modules, custom content elements, ContentBlocks, backend layouts, data processors, FlexForms,
  site sets, PSR-14 events, TYPO3 middleware, or any TYPO3 CMS development task. Also trigger when
  the user mentions ext_emconf, ext_localconf, ext_tables, Services.yaml, composer.json for TYPO3,
  TYPO3 testing, PHPUnit with TYPO3, PHPStan, php-cs-fixer, rector, or TYPO3 code quality.
  Even if the user just says "TYPO3" and wants to build something, use this skill.
---

# TYPO3 v14 Extension Development

Build production-ready TYPO3 v14 extensions following official conventions, modern PHP, and the latest TypoScript/Fluid/TCA patterns. Based on the official TYPO3 documentation and the `tea` best-practices extension.

## When to read reference files

Before generating any extension code, read the relevant reference file(s):

| Task | Reference to read |
|------|-------------------|
| New extension scaffold, composer.json, Services.yaml, TCA, FlexForms | `references/extension-structure.md` |
| TypoScript setup/constants/config, data processors, stdWrap, conditions | `references/typoscript-reference.md` |
| Extbase plugin, controller, model, repository, Fluid templates | `references/extbase-patterns.md` |
| Custom content element or ContentBlock | `references/content-elements.md` |
| Testing, CI, code quality (PHPUnit, PHPStan, php-cs-fixer, rector) | `references/testing-and-quality.md` |
| Fluid ViewHelpers — complete reference | `references/fluid-viewhelpers.md` |
| TCA column types — full type reference | `references/tca-reference.md` |

Read the reference BEFORE writing code. Multiple references often apply — read all relevant ones.

## Core Principles (TYPO3 v14)

### Mandatory Standards

1. **PHP 8.2+ with strict types** — every file: `declare(strict_types=1);`
2. **PSR-14 events only** — no hooks, no signal/slots, no `$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']`
3. **Dependency injection via Services.yaml** — constructor injection, no `GeneralUtility::makeInstance()` for services
4. **Site Sets for TypoScript** — `Configuration/Sets/` with `config.yaml`
5. **`@import` only** — `<INCLUDE_TYPOSCRIPT:` removed in v14
6. **PAGEVIEW for page templates** — convention-based, less boilerplate (v13.1+)
7. **PHP attributes for registration** — `#[AsEventListener]`, `#[AsMiddleware]`, etc.
8. **TCA in Configuration/TCA/** — not in ext_tables.php; auto-generated DB fields for most types
9. **composer.json is mandatory**
10. **All controller actions return `ResponseInterface`**
11. **ContentBlocks for simple content elements** — YAML-based, no PHP needed

### Extension Naming

| Format | Example |
|--------|---------|
| Composer name | `vendor/package-name` |
| Extension key | `package_name` |
| PHP namespace | `Vendor\PackageName` |
| Extbase extension name | `PackageName` |

### File Structure (based on official site_package + tea extension)

```
my_extension/
├── Classes/
│   ├── Controller/
│   ├── Command/               # Symfony console commands
│   ├── Domain/Model/
│   ├── Domain/Repository/
│   ├── DataProcessing/
│   ├── EventListener/
│   └── Middleware/
├── Configuration/
│   ├── Backend/Modules.php
│   ├── Extbase/Persistence/Classes.php
│   ├── FlexForms/
│   ├── Sets/
│   │   └── MyExtension/
│   │       ├── config.yaml
│   │       ├── settings.definitions.yaml  # Typed settings with categories
│   │       ├── settings.yaml              # Override defaults
│   │       ├── setup.typoscript
│   │       ├── constants.typoscript
│   │       ├── page.tsconfig
│   │       └── PageTsConfig/BackendLayouts/
│   ├── TCA/
│   │   ├── Overrides/tt_content.php
│   │   └── tx_myext_domain_model_*.php
│   ├── Icons.php
│   └── Services.yaml
├── ContentBlocks/             # YAML-based content elements (optional)
│   └── ContentElements/
│       └── my-element/
│           ├── config.yaml
│           ├── language/labels.xlf
│           └── templates/frontend.html
├── Resources/
│   ├── Private/
│   │   ├── Language/
│   │   ├── PageView/          # For PAGEVIEW templates
│   │   │   ├── Layouts/
│   │   │   ├── Pages/         # Named after backend layout
│   │   │   └── Partials/
│   │   └── Templates/         # For Extbase plugins
│   └── Public/
├── Tests/
│   ├── Functional/
│   └── Unit/
├── Build/                     # QA tool configs
│   ├── phpunit/
│   ├── phpstan/
│   ├── php-cs-fixer/
│   └── rector/
├── composer.json
├── ext_emconf.php
├── ext_localconf.php
└── ext_tables.sql
```

## Quick Reference: TypoScript (v14)

### PAGEVIEW (preferred for page templates)

```typoscript
page = PAGE
page {
    10 = PAGEVIEW
    10 {
        paths.100 = EXT:my_sitepackage/Resources/Private/PageView/
        dataProcessing {
            10 = page-content
            20 = menu
            30 = menu
            30 {
                special = rootline
                special.range = 0|-1
                as = breadcrumb
            }
        }
    }
    shortcutIcon = {$MyExt.favicon}
}
```

Template resolves from backend layout name → `Pages/Default.html`, `Pages/Subpage.html`, etc.

Default variables: `{page}`, `{language}`, `{site}`, `{settings}`, `{content}` (from page-content processor).

### Rendering content in PAGEVIEW Fluid templates

```html
<f:for each="{content.main.records}" as="record">
    <f:cObject typoscriptObjectPath="{record.mainType}" data="{record}" table="{record.mainType}" />
</f:for>
```

### Site Set with typed settings

```yaml
# Configuration/Sets/MyExtension/config.yaml
name: vendor/my-extension
label: My Extension
dependencies:
  - typo3/fluid-styled-content

# Configuration/Sets/MyExtension/settings.definitions.yaml
settings:
  MyExtension.itemsPerPage:
    label: Items per page
    type: int
    default: 10
```

### Backend Layouts via TSconfig in Sets

```tsconfig
mod.web_layout.BackendLayouts.default {
    title = Default
    config.backend_layout {
        colCount = 1
        rowCount = 2
        rows {
            1.columns.1 {
                name = Stage
                colPos = 1
                identifier = stage
                slideMode = slide
            }
            2.columns.1 {
                name = Main
                colPos = 0
                identifier = main
            }
        }
    }
    icon = EXT:my_ext/Resources/Public/Icons/BackendLayouts/default.svg
}
```

### Asset inclusion in Fluid (preferred over TypoScript includeCSS/JS)

```html
<f:asset.css identifier="main" href="EXT:my_ext/Resources/Public/Css/main.css" />
<f:asset.script identifier="main" src="EXT:my_ext/Resources/Public/JavaScript/main.js" />
```

### ContentBlocks (simple content elements without PHP)

```yaml
# ContentBlocks/ContentElements/my-element/config.yaml
name: vendor/my-element
typeName: vendor_my_element
group: default
fields:
  - identifier: header
    useExistingField: true
  - identifier: bodytext
    useExistingField: true
    enableRichtext: true
  - identifier: custom_field
    type: Text
    max: 255
  - identifier: link
    type: Link
    required: true
```

Template at `templates/frontend.html` with `{data.header}`, `{data.custom_field}`, etc.

## Workflow

When asked to create a TYPO3 extension:

1. **Clarify requirements** — extension type, features, target tables
2. **Read relevant references** from this skill
3. **Generate composer.json + ext_emconf.php** first
4. **Build Configuration/** — Site Set, TCA, Services.yaml, Icons.php, backend layouts
5. **Build Classes/** — models, repositories, controllers, event listeners
6. **Build Resources/** — PageView/Fluid templates, language files, CSS/JS
7. **Generate ext_localconf.php** — plugin registration
8. **Generate ext_tables.sql** — only for fields not auto-generated by TCA
9. **Set up testing** — PHPUnit configs, PHPStan, php-cs-fixer, rector
10. **Add CI pipeline** if requested

Always verify against TYPO3 v14 standards. Never use deprecated patterns. Prefer ContentBlocks for simple CEs, Extbase for complex plugins.
