# TypoScript Reference for TYPO3 v14

Comprehensive reference based on the official TYPO3 TypoScript Reference and site_package tutorial.

## Syntax Fundamentals

```typoscript
foo = value                          # Assignment
foo (                                # Multi-line
  line 1
  line 2
)
foo { bar = v1; baz = v2 }           # Code block
foo >                                # Unset/delete
foo < lib.someObject                 # Copy
foo =< lib.someObject                # Reference (linked)
foo := addToList(a, b)               # Modify
foo := removeFromList(a)
foo := replaceString(old|new)
foo := prependString(prefix)
foo := appendString(suffix)
# Line comment  // Also comment  /* Block comment */
```

Constants: define in `constants.typoscript`, use as `{$myConst}` in `setup.typoscript`.

---

## Site Sets (TypoScript Provider — v14 standard)

Based on the official site_package structure:

```yaml
# Configuration/Sets/MyExtension/config.yaml
name: vendor/my-extension
label: My Extension
dependencies:
  - typo3/fluid-styled-content
  - typo3/fluid-styled-content-css
```

Files in the same directory are auto-loaded:
- `setup.typoscript` — Frontend TypoScript
- `constants.typoscript` — TypoScript constants
- `page.tsconfig` — Page TSconfig (backend TypoScript)
- `settings.definitions.yaml` — Typed settings with categories
- `settings.yaml` — Override settings from dependencies

Settings definitions (editable in Sites module):

```yaml
# settings.definitions.yaml
categories:
  MyExt:
    label: My Extension
settings:
  MyExt.itemsPerPage:
    label: Items per page
    category: MyExt
    type: int
    default: 10
  MyExt.logo:
    label: Logo path
    category: MyExt
    type: string
    default: 'EXT:my_ext/Resources/Public/Images/logo.svg'
```

Use `@import` to split TypoScript into organized files:

```typoscript
# setup.typoscript
@import './TypoScript/*.typoscript'
@import './TypoScript/Navigation/*.typoscript'
```

---

## PAGE Object

```typoscript
page = PAGE
page {
    typeNum = 0
    10 = PAGEVIEW
    10 { ... }

    includeCSS.main = EXT:my_ext/Resources/Public/Css/main.css
    includeCSSLibs.bootstrap = https://cdn.example.com/bootstrap.min.css
    includeJS.main = EXT:my_ext/Resources/Public/JavaScript/main.js
    includeJSFooter.app = EXT:my_ext/Resources/Public/JavaScript/app.js

    meta {
        viewport = width=device-width, initial-scale=1
        robots = index,follow
        og:title.field = title
        og:title.attribute = property
    }

    headerData.10 = TEXT
    headerData.10.value = <link rel="preconnect" href="https://fonts.googleapis.com">

    shortcutIcon = {$MyExt.favicon}

    config {
        pageTitleSeparator = -
        pageTitleSeparator.noTrimWrap = | | |
        admPanel = 1
    }
}
```

Multiple PAGE objects for different output types:

```typoscript
jsonApi = PAGE
jsonApi {
    typeNum = 1
    config.disableAllHeaderCode = 1
    config.additionalHeaders.10.header = Content-type:application/json
    10 = USER
    10.userFunc = Vendor\MyExt\Api\JsonHandler->render
}
```

---

## PAGEVIEW (preferred for page templates)

Convention-based rendering from the site_package tutorial:

```typoscript
page = PAGE
page {
    10 = PAGEVIEW
    10 {
        paths {
            0 = EXT:my_sitepackage/Resources/Private/PageView/
            10 = {$MyExt.template_path}
        }
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
}
```

Directory structure:
```
Resources/Private/PageView/
├── Layouts/PageLayout.html
├── Pages/
│   ├── Default.html           # backend layout "default"
│   └── Subpage.html           # backend layout "subpage"
└── Partials/
    ├── Content.html
    ├── Header.html
    ├── Footer.html
    └── Navigation/
        ├── Menu.html
        └── Breadcrumb.html
```

Default variables: `{page}`, `{language}`, `{site}`, `{settings}`.

Rendering content from page-content processor (from site_package):
```html
<f:for each="{content.main.records}" as="record">
    <f:cObject typoscriptObjectPath="{record.mainType}" data="{record}" table="{record.mainType}" />
</f:for>
```

---

## FLUIDTEMPLATE (alternative to PAGEVIEW)

```typoscript
lib.contentElement = FLUIDTEMPLATE
lib.contentElement {
    templateName = Default
    templateRootPaths.10 = EXT:my_ext/Resources/Private/Templates/
    layoutRootPaths.10 = EXT:my_ext/Resources/Private/Layouts/
    partialRootPaths.10 = EXT:my_ext/Resources/Private/Partials/
    settings { defaultHeaderType = {$styles.content.defaultHeaderType} }
    variables { pageTitle = TEXT; pageTitle.data = page:title }
    dataProcessing { 10 = files; 10.references.fieldName = media; 10.as = images }
}
```

---

## Config Object

```typoscript
config {
    absRefPrefix = auto
    cache_period = 86400
    no_cache = 0
    sendCacheHeaders = 1
    doctype = html5
    disablePrefixComment = 1
    pageTitleFirst = 1
    pageTitleSeparator = |
    pageTitleSeparator.noTrimWrap = | | |
    contentObjectExceptionHandler = 1
    moveJsFromHeaderToFooter = 1
    spamProtectEmailAddresses = -2
    additionalHeaders {
        10.header = X-Frame-Options: SAMEORIGIN
        20.header = Strict-Transport-Security: max-age=31536000
    }
    disableCanonical = 0
    disableHrefLang = 0
}
```

---

## Data Processors

| Alias | Class | Purpose |
|-------|-------|---------|
| `page-content` | `PageContentFetchingProcessor` | Load tt_content by backend layout columns |
| `database-query` | `DatabaseQueryProcessor` | Fetch records with SELECT semantics |
| `menu` | `MenuProcessor` | Navigation menus (levels, rootline, directory, list) |
| `files` | `FilesProcessor` | File references, folders, collections |
| `flex-form` | `FlexFormProcessor` | Parse FlexForm XML |
| `site` | `SiteProcessor` | Current site info |
| `site-language` | `SiteLanguageProcessor` | Current language |
| `record-transformation` | `RecordTransformationProcessor` | Computed record info (v13.2+) |
| `comma-separated-value` | `CommaSeparatedValueProcessor` | Parse CSV |
| `gallery` | `GalleryProcessor` | Image gallery calculations |

Menu processor examples:
```typoscript
# Main nav
20 = menu
20 { levels = 3; expandAll = 0; as = mainMenu }

# Breadcrumb
30 = menu
30 { special = rootline; special.range = 0|-1; as = breadcrumb }

# Directory listing
40 = menu
40 { special = directory; special.value = 42; as = subPages }

# Language menu
50 = site-language
50.as = languageMenu
```

Custom data processor:
```php
final class MyProcessor implements DataProcessorInterface
{
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration,
        array $processorConfiguration, array $processedData): array
    {
        $processedData['myVar'] = 'value';
        return $processedData;
    }
}
```

---

## stdWrap Essentials

**Getting data:** `data`, `field`, `current`, `cObject`
**Processing:** `trim`, `intval`, `required`, `if`, `wrap`, `noTrimWrap`, `dataWrap`, `case`, `crop`, `stripHtml`, `htmlSpecialChars`, `replacement`, `split`, `typolink`
**Overriding:** `override`, `ifEmpty`, `ifBlank`

getData sources: `page:title`, `page:uid`, `date:U`, `site:base`, `site:websiteTitle`, `siteLanguage:title`, `siteLanguage:locale`, `leveltitle:0`, `leveluid:1`, `register:myVar`, `getenv:HTTP_HOST`, `GP:tx_myext|action`, `fullRootLine:-1,title`

---

## Conditions (Symfony ExpressionLanguage)

```typoscript
[applicationContext == "Development"]
[traverse(page, "uid") == 42]
[traverse(page, "uid") in [1, 5, 42]]
[tree.level == 0]
[tree.pagelayout === "pagets__Home"]
[frontend.user.isLoggedIn]
[siteLanguage("locale") == "de_DE"]
[site("identifier") == "my-site"]
[date("H") >= 8 && date("H") <= 18]
[ip("192.168.1.*")]
```

Use `traverse()` for sub-arrays to avoid warnings. Combine with `and`/`or`.

---

## @import Rules (v14)

```typoscript
@import 'EXT:my_ext/Configuration/TypoScript/setup.typoscript'
@import 'EXT:my_ext/Configuration/TypoScript/*.typoscript'
@import './partials/navigation.typoscript'
```

- Files MUST end with `.typoscript`; quotes required
- Wildcards only on file level; paths must start with `EXT:` or `./`
- No `../` traversal; no recursive directory imports
- `<INCLUDE_TYPOSCRIPT:` is **removed** in v14

---

## Backend Layouts via Page TSconfig

From the site_package:

```tsconfig
# Configuration/Sets/MyExt/page.tsconfig
@import './PageTsConfig/'
@import './PageTsConfig/BackendLayouts/'
```

```tsconfig
# PageTsConfig/BackendLayouts/default.tsconfig
mod.web_layout.BackendLayouts.default {
    title = LLL:my_ext.backend.layouts:default
    config.backend_layout {
        colCount = 1
        rowCount = 2
        rows {
            1.columns.1 { name = Stage; colPos = 1; identifier = stage; slideMode = slide }
            2.columns.1 { name = Main; colPos = 0; identifier = main }
        }
    }
    icon = EXT:my_ext/Resources/Public/Icons/BackendLayouts/default.svg
}
```

Column `identifier` maps to Fluid variable names: `{content.main}`, `{content.stage}`.

`slideMode` options: `slide` (inherit from parent pages), `collect` (collect from all parent pages), `collectReverse`.

---

## Content Object Types

| cObject | Usage |
|---------|-------|
| `PAGEVIEW` | Page templates with convention-based paths (v13.1+) |
| `FLUIDTEMPLATE` | Fluid-based rendering with explicit config |
| `EXTBASEPLUGIN` | Render Extbase plugins: `extensionName`, `pluginName` |
| `TEXT` | Simple text with stdWrap |
| `COA` / `COA_INT` | Array of cObjects (cached / uncached) |
| `HMENU` | Hierarchical menus |
| `IMAGE` / `IMG_RESOURCE` | Image rendering |
| `FILES` | File rendering |
| `RECORDS` | Render specific records |
| `USER` / `USER_INT` | Custom PHP rendering |
| `CASE` | Switch/case |
| `SVG` | SVG rendering |

---

## Extbase Plugin TypoScript

```typoscript
plugin.tx_myextension {
    view {
        templateRootPaths.10 = EXT:my_ext/Resources/Private/Templates/
        partialRootPaths.10 = EXT:my_ext/Resources/Private/Partials/
        layoutRootPaths.10 = EXT:my_ext/Resources/Private/Layouts/
    }
    persistence { storagePid = 42; recursive = 2 }
    settings { itemsPerPage = 10 }
    _LOCAL_LANG.de.list.header = Meine Einträge
}
```

Plugin-specific: `plugin.tx_myextension_listplugin.settings.itemsPerPage = 20`
