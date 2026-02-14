---
name: typo3-content-blocks
description: >-
  Expert in TYPO3 Content Blocks for creating flexible content elements. Activates when developing
  content elements, working with FlexForms, creating custom content types, or migrating traditional
  elements; or when the user mentions content blocks, content elements, FlexForms, or tt_content.
---

# TYPO3 Content Blocks Specialist

## When to Apply

Activate this skill when:

- Creating new content elements
- Working with FlexForms for element configuration
- Designing flexible content structures
- Migrating traditional content elements
- Optimizing content element rendering
- Integrating content elements with Fluid templates

## Core Expertise Areas

### Content Element Development

**Core-native approach (TYPO3 v14):**
- Use standard `tt_content` fields only
- Configure with FlexForms for element-specific settings
- No custom database columns
- Register via TCA and PageTSconfig

**Content element registration:**
- TCA configuration in `Configuration/TCA/Overrides/tt_content.php`
- PageTSconfig for backend display
- Icon registration in `Configuration/Icons.php`

### FlexForm Configuration

**FlexForm structure:**
- XML-based configuration in `Configuration/FlexForms/`
- Sheet organization for grouped settings
- Field types and validation
- Display conditions

**Best practices:**
- Group related fields in sheets
- Use meaningful field names
- Provide clear labels and descriptions
- Implement proper validation

### Template Integration

**Fluid templates:**
- Template paths in extension configuration
- Access FlexForm data in templates
- Responsive design implementation
- Accessibility considerations

**DataProcessors:**
- Prepare data before template rendering
- Process files and images
- Query database records
- Transform data structures

## Project Context (mens_circle)

**Extension structure:**
```
packages/mens_circle/
  Configuration/
    FlexForms/         # FlexForm configurations
    TCA/Overrides/     # TCA customizations
    Icons.php          # Icon registration
  Resources/
    Private/
      Fluid/
        Templates/     # Content element templates
        Partials/      # Reusable template parts
      Frontend/        # Assets (compiled with Vite)
```

**Current content elements:**
- Event list display
- Newsletter subscription forms
- Testimonial displays
- Hero sections
- Text and media combinations

**Integration points:**
- DataProcessors fetch events, registrations
- Templates use Hotwire Turbo for dynamic updates
- Frontend compiled with Bun + Vite

## Content Element Patterns

### Registration Pattern

**1. TCA Configuration** (`Configuration/TCA/Overrides/tt_content.php`):
```php
// Add new CType
$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
    'label' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_db.xlf:tt_content.CType.menscircle_event',
    'value' => 'menscircle_event',
    'icon' => 'menscircle-event',
    'group' => 'menscircle',
];

// Configure the content element
$GLOBALS['TCA']['tt_content']['types']['menscircle_event'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
            pi_flexform,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
    'columnsOverrides' => [
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'default' => 'FILE:EXT:mens_circle/Configuration/FlexForms/Event.xml',
                ],
            ],
        ],
    ],
];
```

**2. FlexForm Definition** (`Configuration/FlexForms/Event.xml`):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Settings</sheetTitle>
                <type>array</type>
                <el>
                    <settings.mode>
                        <label>Display Mode</label>
                        <config>
                            <type>select</type>
                            <renderType>selectSingle</renderType>
                            <items>
                                <numIndex index="0">
                                    <label>List</label>
                                    <value>list</value>
                                </numIndex>
                                <numIndex index="1">
                                    <label>Detail</label>
                                    <value>detail</value>
                                </numIndex>
                            </items>
                        </config>
                    </settings.mode>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
```

**3. PageTSconfig** (for backend display):
```typoscript
mod.wizards.newContentElement.wizardItems.menscircle {
    header = Men's Circle
    elements {
        menscircle_event {
            iconIdentifier = menscircle-event
            title = Event Display
            description = Display events list or detail
            tt_content_defValues {
                CType = menscircle_event
            }
        }
    }
    show := addToList(menscircle_event)
}
```

### Fluid Template Pattern

**Template file** (`Resources/Private/Fluid/Templates/Event.html`):
```html
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">

<f:layout name="Default" />

<f:section name="Main">
    <div class="event-element">
        <f:if condition="{data.pi_flexform.settings.mode} == 'list'">
            <f:for each="{events}" as="event">
                <div class="event-card">
                    <h3>{event.title}</h3>
                    <f:format.date format="d.m.Y">{event.date}</f:format.date>
                </div>
            </f:for>
        </f:if>
    </div>
</f:section>

</html>
```

**DataProcessor** (prepare data):
```php
class NextEventDataProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository
    ) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $events = $this->eventRepository->findUpcoming();
        $processedData['events'] = $events;
        return $processedData;
    }
}
```

## Best Practices

### Content Element Design

- Use Core fields where possible (header, bodytext, assets)
- Keep FlexForms simple and focused
- Provide meaningful backend labels
- Include help text for editors

### Template Development

- Create responsive and accessible templates
- Follow Fluid best practices
- Optimize asset loading
- Implement proper error handling

### Performance

- Use DataProcessors for data preparation
- Implement caching where appropriate
- Optimize database queries
- Minimize frontend dependencies

### Maintainability

- Document FlexForm fields clearly
- Use consistent naming conventions
- Keep templates modular with partials
- Version control FlexForm schemas

## Common Tasks

### Creating New Content Element

1. Define TCA configuration with CType
2. Create FlexForm for settings (if needed)
3. Register icon in `Configuration/Icons.php`
4. Add PageTSconfig for backend wizard
5. Create Fluid template
6. Add DataProcessor if data fetching needed
7. Test in backend and frontend

### Adding FlexForm Fields

1. Open FlexForm XML file
2. Add field definition in appropriate sheet
3. Configure field type and validation
4. Update Fluid template to use new field
5. Clear TYPO3 caches

### Optimizing Content Elements

1. Review DataProcessor efficiency
2. Implement element-specific caching
3. Optimize Fluid template rendering
4. Minimize database queries
5. Use asset pipeline for resources

## Tools Used

- **View**: Examine TCA and FlexForm configurations
- **Grep**: Find content element patterns
- **Bash**: Clear TYPO3 caches after changes

## Related Skills

- **typo3-fluid**: For advanced Fluid template development
- **typo3-extension-dev**: For custom DataProcessors and logic
- **typo3-architect**: For content architecture design
