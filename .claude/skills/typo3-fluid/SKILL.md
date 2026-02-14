---
name: typo3-fluid
description: >-
  Fluid templating expert for advanced TYPO3 template development, ViewHelpers, and frontend
  optimization. Activates when working with Fluid templates, creating ViewHelpers, optimizing
  template performance, or implementing responsive designs; or when the user mentions Fluid,
  templates, ViewHelpers, or template optimization.
---

# TYPO3 Fluid Expert

## When to Apply

Activate this skill when:

- Creating or modifying Fluid templates
- Developing custom ViewHelpers
- Optimizing template rendering and performance
- Implementing responsive and accessible designs
- Working with template layouts and partials
- Integrating frontend assets with templates

## Core Expertise Areas

### Advanced Templating

**Template structure:**
```
Resources/Private/Fluid/
  Layouts/
    Default.html         # Base layout
    Page.html           # Page layout
  Templates/
    Event/
      List.html         # Event list template
      Detail.html       # Event detail template
  Partials/
    Header.html         # Reusable header
    Footer.html         # Reusable footer
```

**Layout pattern:**
```html
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">

<f:layout name="Default" />

<f:section name="Main">
    <div class="content">
        <!-- Template content -->
    </div>
</f:section>

</html>
```

**Layout file** (`Layouts/Default.html`):
```html
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{pageTitle}</title>
    <f:asset.css identifier="main" href="EXT:mens_circle/Resources/Public/Css/main.css" />
</head>
<body>
    <f:render partial="Header" arguments="{_all}" />
    
    <main>
        <f:render section="Main" />
    </main>
    
    <f:render partial="Footer" arguments="{_all}" />
    
    <f:asset.script identifier="main" src="EXT:mens_circle/Resources/Public/JavaScript/main.js" />
</body>
</html>
```

### ViewHelpers

**Built-in ViewHelpers:**
- `f:for` - Loop over arrays/objects
- `f:if` - Conditional rendering
- `f:format.*` - Format data (date, number, html, etc.)
- `f:link.typolink` - Create links
- `f:uri.typolink` - Generate URLs
- `f:image` - Render images
- `f:asset.*` - Manage CSS/JS assets

**Custom ViewHelper** (`Classes/ViewHelpers/FormatPhoneViewHelper.php`):
```php
namespace Vendor\Extension\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FormatPhoneViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('number', 'string', 'Phone number to format', true);
    }

    public function render(): string
    {
        $number = $this->arguments['number'];
        
        // Format phone number
        $formatted = preg_replace('/[^0-9+]/', '', $number);
        
        return '<a href="tel:' . $formatted . '">' . $number . '</a>';
    }
}
```

**Usage in template:**
```html
{namespace ext=Vendor\Extension\ViewHelpers}

<ext:formatPhone number="{participant.phone}" />
```

### Common Fluid Patterns

**Looping with f:for:**
```html
<f:for each="{events}" as="event" iteration="iterator">
    <div class="event-card {f:if(condition: iterator.isFirst, then: 'first')}">
        <h3>{event.title}</h3>
        <f:format.date format="d.m.Y">{event.date}</f:format.date>
    </div>
</f:for>
```

**Conditionals with f:if:**
```html
<f:if condition="{event.isFull}">
    <f:then>
        <span class="badge badge-danger">Ausgebucht</span>
    </f:then>
    <f:else>
        <f:link.typolink parameter="{event.registrationPage}">
            Jetzt anmelden
        </f:link.typolink>
    </f:else>
</f:if>
```

**Inline syntax (for simple cases):**
```html
<div class="{f:if(condition: event.highlighted, then: 'highlighted', else: 'normal')}">
    {event.title}
</div>

<!-- Or with inline notation -->
<div class="{event.highlighted ? 'highlighted' : 'normal'}">
    {event.title}
</div>
```

**Links with f:link.typolink:**
```html
<!-- Internal page -->
<f:link.typolink parameter="{page.uid}">
    {page.title}
</f:link.typolink>

<!-- With anchor -->
<f:link.typolink parameter="{page.uid}#section">
    Jump to section
</f:link.typolink>

<!-- External with attributes -->
<f:link.typolink 
    parameter="{event.externalUrl}" 
    additionalAttributes="{target: '_blank', rel: 'noopener noreferrer'}">
    External Link
</f:link.typolink>
```

**Images with f:image:**
```html
<f:image 
    image="{event.image}" 
    width="800c" 
    height="600c" 
    alt="{event.title}"
    loading="lazy"
    class="event-image" />
```

**Asset management:**
```html
<!-- CSS -->
<f:asset.css 
    identifier="event-styles"
    href="EXT:mens_circle/Resources/Public/Css/events.css"
    priority="10" />

<!-- JavaScript -->
<f:asset.script 
    identifier="event-script"
    src="EXT:mens_circle/Resources/Public/JavaScript/events.js"
    priority="10" />

<!-- Inline CSS -->
<f:asset.css identifier="inline-styles">
    .special-event { color: red; }
</f:asset.css>
```

### Data Access

**Access FlexForm data:**
```html
<!-- Content element with FlexForm -->
{data.pi_flexform.settings.mode}
{data.pi_flexform.settings.limit}
```

**Access processed data:**
```html
<!-- Data from DataProcessor -->
<f:for each="{events}" as="event">
    <!-- events is provided by DataProcessor -->
</f:for>
```

**Access page data:**
```html
{page.title}
{page.uid}
{page.layout}
```

## Project Context (mens_circle)

**Frontend build:**
- Uses Bun + Vite for asset compilation
- Source: `packages/mens_circle/Resources/Private/Frontend/`
- Output: `packages/mens_circle/Resources/Public/`
- Build: `bun run build`
- Dev: `bun run dev`

**Hotwire Turbo integration:**
- Templates must support Turbo Drive
- Reinitialize components on `turbo:load`
- Use `data-turbo="false"` for full page reloads when needed

**Current templates:**
- Event list and detail views
- Newsletter subscription forms
- Content element templates
- Page layouts with responsive design

## Best Practices

### Template Development

**Structure:**
- Use layouts for base HTML structure
- Create reusable partials for common elements
- Keep templates focused and single-purpose
- Use sections for overridable content areas

**Readability:**
- Use meaningful variable names
- Add comments for complex logic
- Indent properly for nested structures
- Keep inline conditions simple

**Performance:**
- Avoid complex logic in templates
- Use DataProcessors for data preparation
- Implement proper caching
- Minimize ViewHelper calls in loops

### Responsive Design

**Mobile-first approach:**
```html
<div class="event-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <f:for each="{events}" as="event">
        <div class="event-card">
            <!-- Event content -->
        </div>
    </f:for>
</div>
```

**Responsive images:**
```html
<f:image 
    image="{event.image}"
    srcset="480, 800, 1200"
    sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw"
    alt="{event.title}" />
```

### Accessibility

**Semantic HTML:**
```html
<article class="event">
    <header>
        <h2>{event.title}</h2>
    </header>
    <div class="event-content">
        <f:format.html>{event.description}</f:format.html>
    </div>
    <footer>
        <time datetime="{f:format.date(date: event.date, format: 'Y-m-d')}">
            <f:format.date format="d.m.Y">{event.date}</f:format.date>
        </time>
    </footer>
</article>
```

**ARIA attributes:**
```html
<button 
    type="button"
    aria-expanded="false"
    aria-controls="event-details-{event.uid}">
    Details anzeigen
</button>

<div id="event-details-{event.uid}" aria-hidden="true">
    <!-- Details -->
</div>
```

### Security

**Escape output by default:**
```html
<!-- Escaped by default -->
{event.title}

<!-- Explicitly escape -->
<f:format.htmlspecialchars>{event.userInput}</f:format.htmlspecialchars>

<!-- Allow HTML (only for trusted content) -->
<f:format.html>{event.richTextContent}</f:format.html>
```

**Sanitize HTML:**
```html
<!-- Use format.html for rich text from editors -->
<f:format.html parseFuncTSPath="lib.parseFunc_RTE">
    {event.bodytext}
</f:format.html>
```

## Common Tasks

### Creating New Template

1. Create template file in `Resources/Private/Fluid/Templates/`
2. Define layout with `<f:layout name="Default" />`
3. Create main section `<f:section name="Main">`
4. Use ViewHelpers and partials as needed
5. Configure template paths in TypoScript or site config

### Creating Custom ViewHelper

1. Create class in `Classes/ViewHelpers/`
2. Extend `AbstractViewHelper` or `AbstractTagBasedViewHelper`
3. Implement `render()` method
4. Register namespace in template
5. Use in templates with namespace prefix

### Optimizing Template Performance

1. Move data processing to DataProcessors
2. Implement caching for heavy operations
3. Minimize ViewHelper calls in loops
4. Use partials efficiently
5. Optimize asset loading

### Debugging Templates

**Enable debug output:**
```html
<f:debug>{_all}</f:debug>
<f:debug>{event}</f:debug>
```

**Check variable type:**
```html
<f:debug inline="true">{event}</f:debug>
```

## ViewHelper Reference

**Common ViewHelpers:**
- `f:link.page` - Link to page by UID
- `f:link.typolink` - Universal link (page, external, file, etc.)
- `f:uri.page` - Generate page URL
- `f:uri.typolink` - Generate typolink URL
- `f:format.date` - Format dates
- `f:format.html` - Parse RTE content
- `f:format.crop` - Truncate text
- `f:count` - Count array elements
- `f:spaceless` - Remove whitespace

**Form ViewHelpers:**
- `f:form` - Form container
- `f:form.textfield` - Text input
- `f:form.textarea` - Textarea
- `f:form.select` - Select dropdown
- `f:form.checkbox` - Checkbox
- `f:form.submit` - Submit button

## Tools Used

- **View**: Examine Fluid templates
- **Grep**: Find ViewHelper usage patterns
- **Edit/Create**: Modify templates and ViewHelpers

## Related Skills

- **typo3-content-blocks**: For content element templates
- **typo3-extension-dev**: For custom ViewHelper development
- **tailwindcss-development**: For styling templates
