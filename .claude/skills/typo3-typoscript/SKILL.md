---
name: typo3-typoscript
description: >-
  TypoScript expert for advanced TYPO3 configuration, site setup, and performance optimization.
  Activates when working with TypoScript, site configuration, routing, caching, or troubleshooting
  TYPO3 configuration issues; or when the user mentions TypoScript, site config, routing, or TYPO3 setup.
---

# TYPO3 TypoScript Expert

## When to Apply

Activate this skill when:

- Writing or modifying TypoScript configuration
- Setting up site configuration (config.yaml)
- Configuring routing and URL handling
- Implementing caching strategies
- Troubleshooting TypoScript issues
- Migrating legacy TypoScript
- Optimizing TYPO3 performance via configuration

## Core Expertise Areas

### Modern Site Configuration

**Site configuration** (`config/sites/*/config.yaml`):
```yaml
rootPageId: 1
base: 'https://example.com/'
baseVariants:
  - base: 'https://dev.example.com/'
    condition: 'applicationContext == "Development"'

languages:
  - languageId: 0
    title: Deutsch
    navigationTitle: DE
    base: /
    locale: de_DE.UTF-8
    iso-639-1: de
    hreflang: de-DE
    direction: ltr
    flag: de
    enabled: true

  - languageId: 1
    title: English
    navigationTitle: EN
    base: /en/
    locale: en_GB.UTF-8
    iso-639-1: en
    hreflang: en-GB
    direction: ltr
    fallbackType: strict
    flag: gb
    enabled: true

errorHandling:
  - errorCode: 404
    errorHandler: Page
    errorContentSource: 't3://page?uid=123'

routeEnhancers:
  EventDetail:
    type: Extbase
    limitToPages: [10]
    extension: MensCircle
    plugin: Event
    routes:
      - routePath: '/{event_slug}'
        _controller: 'Event::detail'
        _arguments:
          event_slug: event
    defaultController: 'Event::list'
    aspects:
      event_slug:
        type: PersistedAliasMapper
        tableName: tx_menscircle_domain_model_event
        routeFieldName: slug
```

### Site Sets (TYPO3 v14)

**Site Set configuration** (`Configuration/Sets/MensCircle/config.yaml`):
```yaml
name: menscircle/site-set
label: Men's Circle Site Configuration

dependencies:
  - typo3/fluid-styled-content

optionalDependencies:
  - felogin/felogin

settings:
  menscircle:
    eventList:
      itemsPerPage: 10
    newsletter:
      fromEmail: noreply@menscircle.com
      fromName: Men's Circle

# TypoScript is loaded automatically from:
# - setup.typoscript
# - constants.typoscript
# - page.tsconfig
```

### TypoScript Configuration

**Page object** (`Configuration/TypoScript/setup.typoscript`):
```typoscript
page = PAGE
page {
    typeNum = 0
    
    10 = FLUIDTEMPLATE
    10 {
        templateName = TEXT
        templateName.stdWrap.cObject = CASE
        templateName.stdWrap.cObject {
            key.data = pagelayout
            
            default = TEXT
            default.value = Default
            
            1 = TEXT
            1.value = Homepage
            
            2 = TEXT
            2.value = ContentPage
        }
        
        templateRootPaths {
            10 = EXT:mens_circle/Resources/Private/Fluid/Templates/Page/
        }
        
        partialRootPaths {
            10 = EXT:mens_circle/Resources/Private/Fluid/Partials/
        }
        
        layoutRootPaths {
            10 = EXT:mens_circle/Resources/Private/Fluid/Layouts/
        }
        
        dataProcessing {
            10 = TYPO3\CMS\Frontend\DataProcessing\MenuProcessor
            10 {
                levels = 2
                as = mainNavigation
            }
            
            20 = Vendor\MensCircle\DataProcessing\NextEventDataProcessor
            20 {
                as = nextEvent
            }
        }
        
        variables {
            pageTitle = TEXT
            pageTitle.data = page:title
            
            contentMain < styles.content.get
            
            contentSidebar < styles.content.get
            contentSidebar.select.where = {#colPos}=1
        }
    }
    
    # Include CSS
    includeCSS {
        main = EXT:mens_circle/Resources/Public/Css/main.css
    }
    
    # Include JavaScript
    includeJSFooter {
        main = EXT:mens_circle/Resources/Public/JavaScript/main.js
        main.defer = 1
    }
    
    # Meta tags
    meta {
        viewport = width=device-width, initial-scale=1.0
        description.data = page:description
        keywords.data = page:keywords
    }
}
```

**Content rendering** (`lib.contentElement`):
```typoscript
lib.contentElement = FLUIDTEMPLATE
lib.contentElement {
    templateName = TEXT
    templateName.stdWrap.cObject = CASE
    templateName.stdWrap.cObject {
        key.field = CType
        
        default = TEXT
        default.value = Default
        
        menscircle_event = TEXT
        menscircle_event.value = Event
        
        menscircle_newsletter = TEXT
        menscircle_newsletter.value = Newsletter
    }
    
    templateRootPaths {
        10 = EXT:mens_circle/Resources/Private/Fluid/Templates/Content/
    }
    
    partialRootPaths {
        10 = EXT:mens_circle/Resources/Private/Fluid/Partials/
    }
    
    layoutRootPaths {
        10 = EXT:mens_circle/Resources/Private/Fluid/Layouts/
    }
    
    dataProcessing {
        10 = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
        10 {
            references.fieldName = assets
            as = files
        }
    }
    
    settings {
        # Pass settings to templates
    }
}

# Register content element rendering
tt_content.menscircle_event =< lib.contentElement
tt_content.menscircle_newsletter =< lib.contentElement
```

### Routing Configuration

**Route enhancers for pretty URLs:**
```yaml
routeEnhancers:
  EventDetail:
    type: Extbase
    limitToPages: [10]  # Event list page UID
    extension: MensCircle
    plugin: Event
    routes:
      - routePath: '/{event_slug}'
        _controller: 'Event::detail'
        _arguments:
          event_slug: event
    aspects:
      event_slug:
        type: PersistedAliasMapper
        tableName: tx_menscircle_domain_model_event
        routeFieldName: slug

  PageTypeSuffix:
    type: PageType
    default: '/'
    map:
      '.json': 1234
      '.xml': 1235
```

### Caching Configuration

**Page caching:**
```typoscript
config {
    # Enable caching
    cache_period = 86400
    sendCacheHeaders = 1
    
    # Cache headers
    sendCacheHeaders_onlyWhenLoginDeniedInBranch = 1
    
    # Disable cache for dynamic content
    no_cache = 0
}

# Disable cache for specific pages
[page["uid"] == 10]
    config.no_cache = 1
[END]
```

**Content element caching:**
```typoscript
tt_content.menscircle_event.cache {
    # Cache for 1 hour
    lifetime = 3600
    
    # Cache tags
    tags = menscircle_event_{field:uid}
}
```

### Conditions

**TypoScript conditions:**
```typoscript
# Application context
[applicationContext == "Development"]
    config.debug = 1
[END]

# Page UID
[page["uid"] == 1]
    page.bodyTagAdd = class="homepage"
[END]

# Backend layout
[page["backend_layout"] == "pagets__1"]
    page.10.templateName = TEXT
    page.10.templateName.value = Homepage
[END]

# User login status
[frontend.user.isLoggedIn]
    page.10.variables.userMenu < lib.userMenu
[END]

# Time-based
[date("H") >= 18 || date("H") < 8]
    page.bodyTagAdd = class="night-mode"
[END]
```

### Constants and Configuration

**Constants** (`Configuration/TypoScript/constants.typoscript`):
```typoscript
menscircle {
    view {
        templateRootPath = EXT:mens_circle/Resources/Private/Fluid/Templates/
        partialRootPath = EXT:mens_circle/Resources/Private/Fluid/Partials/
        layoutRootPath = EXT:mens_circle/Resources/Private/Fluid/Layouts/
    }
    
    settings {
        eventList {
            itemsPerPage = 10
        }
    }
}
```

**Global configuration:**
```typoscript
config {
    # Language
    language = de
    locale_all = de_DE.UTF-8
    sys_language_uid = 0
    
    # Links
    linkVars = L
    uniqueLinkVars = 1
    
    # Security
    spamProtectEmailAddresses = 2
    spamProtectEmailAddresses_atSubst = (at)
    
    # HTML
    doctype = html5
    htmlTag_setParams = lang="de"
    removeDefaultJS = 0
    inlineStyle2TempFile = 1
    
    # Compression
    compressJs = 1
    compressCss = 1
    concatenateJs = 1
    concatenateCss = 1
    
    # Debug (only for development)
    debug = 0
    admPanel = 0
}
```

## Project Context (mens_circle)

**Site configuration:**
- Location: `config/sites/mens-circle/config.yaml`
- Root page UID: 1
- Single language (German)
- Event detail routing configured

**TypoScript location:**
- Site Set: `packages/mens_circle/Configuration/Sets/MensCircle/`
- Files: `setup.typoscript`, `constants.typoscript`, `page.tsconfig`

**Content elements:**
- Registered via TCA
- Template rendering via `lib.contentElement`
- Custom DataProcessors for event data

## Best Practices

### Code Organization

**Modular structure:**
```
Configuration/
  Sets/
    MensCircle/
      config.yaml           # Site Set configuration
      setup.typoscript      # Main TypoScript setup
      constants.typoscript  # Constants
      page.tsconfig         # Page TSconfig
  TypoScript/
    Setup/
      page.typoscript       # Page configuration
      content.typoscript    # Content element configuration
      lib.typoscript        # Reusable objects
```

**Use constants for reusable values:**
```typoscript
# Constants
plugin.tx_menscircle.settings.itemsPerPage = 10

# Setup - use constant
plugin.tx_menscircle.settings.itemsPerPage = {$plugin.tx_menscircle.settings.itemsPerPage}
```

### Performance

**Enable caching:**
- Set appropriate cache lifetimes
- Use cache tags for targeted clearing
- Enable cache headers for browser caching

**Optimize asset loading:**
- Concatenate and compress CSS/JS
- Use defer/async for JavaScript
- Implement critical CSS

**Minimize TypoScript complexity:**
- Avoid deep nesting
- Use references (`<`) for reusable configuration
- Keep conditions simple and necessary

### Maintenance

**Document configuration:**
- Add comments for complex logic
- Use meaningful object names
- Organize related configuration together

**Version control:**
- Track all TypoScript files
- Use consistent formatting
- Review changes carefully

## Common Tasks

### Adding New Content Element

```typoscript
# Register rendering
tt_content.my_element =< lib.contentElement
tt_content.my_element {
    templateName = TEXT
    templateName.value = MyElement
    
    dataProcessing {
        10 = Vendor\Extension\DataProcessing\MyDataProcessor
    }
}
```

### Configuring Page Layout

```typoscript
page = PAGE
page {
    typeNum = 0
    10 = FLUIDTEMPLATE
    10 {
        # Template configuration
    }
}
```

### Setting Up Multi-Language

```yaml
# config.yaml
languages:
  - languageId: 0
    title: German
    base: /
    locale: de_DE.UTF-8
  - languageId: 1
    title: English
    base: /en/
    locale: en_GB.UTF-8
    fallbackType: strict
```

### Debugging TypoScript

```typoscript
# Enable debug mode
config.debug = 1
config.admPanel = 1

# Debug specific object
page.10 >
page.10 = TEXT
page.10.value = Debug output
```

## Troubleshooting

**Common issues:**

1. **No output:** Check template paths and page object configuration
2. **Wrong data:** Verify DataProcessor configuration
3. **Caching issues:** Clear all caches with `typo3 cache:flush`
4. **Routing errors:** Check routeEnhancers configuration
5. **Conditions not working:** Verify condition syntax and context

**Debugging tools:**
- Admin Panel (enable in user settings)
- TypoScript Object Browser (in backend)
- Cache clearing: `ddev exec vendor/bin/typo3 cache:flush`

## Tools Used

- **View**: Examine TypoScript and site configuration
- **Grep**: Find configuration patterns
- **Bash**: Clear TYPO3 caches
- **Edit**: Modify configuration files

## Related Skills

- **typo3-architect**: For overall site architecture
- **typo3-fluid**: For template integration
- **typo3-extension-dev**: For extension configuration
