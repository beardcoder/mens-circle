# Custom Content Elements for TYPO3 v14

Two approaches: **ContentBlocks** (YAML-based, no PHP) and **traditional** (TCA + TypoScript + Fluid).

---

## ContentBlocks (Recommended for Simple CEs)

Available via `friendsoftypo3/content-blocks`. YAML-driven, no PHP needed.

### Structure

```
ContentBlocks/ContentElements/my-element/
├── config.yaml
├── language/labels.xlf
├── templates/
│   ├── frontend.html
│   └── backend-preview.html    # optional
└── assets/                     # optional
    ├── frontend.css
    ├── frontend.js
    └── icon.svg
```

### config.yaml

```yaml
name: vendor/my-element
typeName: vendor_my_element
group: default          # Group in New CE wizard
prefixFields: true      # Prefix custom fields with vendor_
prefixType: full        # full | vendor

fields:
  # Reuse existing tt_content fields
  - identifier: header
    useExistingField: true

  - identifier: bodytext
    useExistingField: true
    enableRichtext: true

  # Custom fields (auto-create DB columns)
  - identifier: button_text
    type: Text
    default: 'Read more'
    min: 4
    max: 255
    required: true

  - identifier: button_link
    type: Link
    required: true

  - identifier: image
    type: File
    allowed: common-image-types
    minitems: 0
    maxitems: 5

  - identifier: layout_variant
    type: Select
    items:
      - label: Default
        value: default
      - label: Wide
        value: wide
    default: default

  - identifier: show_date
    type: Checkbox

  # Nested collection (IRRE-style)
  - identifier: slides
    type: Collection
    minitems: 1
    maxitems: 10
    appearance:
      collapseAll: true
      levelLinksPosition: both
    fields:
      - identifier: slide_image
        type: File
        allowed: common-image-types
        minitems: 1
        relationship: manyToOne
      - identifier: slide_title
        type: Text
      - identifier: slide_description
        type: Textarea
        enableRichtext: true
```

**ContentBlock field types**: Text, Textarea, Email, Number, Link, Color, Datetime, File, Select, Radio, Checkbox, Category, Collection, FlexForm, Folder, Slug, Uuid, Json, Password, Country.

### Frontend Template

```html
<!-- templates/frontend.html -->
<f:asset.css identifier="my-element-css" href="{cb:assetPath()}/frontend.css" />
<f:asset.script identifier="my-element-js" src="{cb:assetPath()}/frontend.js" />

<div class="my-element my-element--{data.layout_variant}">
    <h2>{data.header}</h2>
    <div class="my-element__body">
        <f:format.html>{data.bodytext}</f:format.html>
    </div>

    <f:if condition="{data.image}">
        <f:for each="{data.image}" as="img">
            <f:image image="{img}" maxWidth="800" class="img-fluid" loading="lazy" />
        </f:for>
    </f:if>

    <f:if condition="{data.button_link}">
        <f:link.typolink parameter="{data.button_link}" class="btn btn-primary">
            {data.button_text}
        </f:link.typolink>
    </f:if>

    <f:if condition="{data.slides}">
        <div class="slides">
            <f:for each="{data.slides}" as="slide" iteration="iter">
                <div class="slide {f:if(condition: iter.isFirst, then: 'active')}">
                    <f:image image="{slide.slide_image}" maxWidth="1200" class="d-block w-100" />
                    <h3>{slide.slide_title}</h3>
                    <f:format.html>{slide.slide_description}</f:format.html>
                </div>
            </f:for>
        </div>
    </f:if>
</div>
```

All field data accessible via `{data.fieldIdentifier}`. Use `{cb:assetPath()}` for assets and `{cb:languagePath()}` for translations.

### Language Labels

```xml
<!-- language/labels.xlf -->
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="labels">
        <header/>
        <body>
            <trans-unit id="title" resname="title">
                <source>My Element</source>
            </trans-unit>
            <trans-unit id="description" resname="description">
                <source>A custom content element</source>
            </trans-unit>
            <trans-unit id="button_text.label" resname="button_text.label">
                <source>Button Text</source>
            </trans-unit>
        </body>
    </file>
</xliff>
```

---

## Traditional Content Elements (TCA + TypoScript + Fluid)

For complex CEs that need data processors or custom PHP logic.

### 1. Register CType — Configuration/TCA/Overrides/tt_content.php

```php
<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$key = 'myextension_teaser';

// Add to CType dropdown and New CE wizard
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:ce.teaser.title',
        'value' => $key,
        'icon' => 'my-extension-teaser',
        'group' => 'default',
        'description' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:ce.teaser.description',
    ],
);

// v13.3+: system fields auto-added — only list custom fields
$GLOBALS['TCA']['tt_content']['types'][$key] = [
    'showitem' => '
        --palette--;;headers,
        bodytext,
        tx_myextension_link,
        image,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],
    ],
];

// Add custom field to tt_content
ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'tx_myextension_link' => [
        'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:field.link',
        'config' => [
            'type' => 'link',
        ],
    ],
]);
```

### 2. Add custom DB fields — ext_tables.sql

```sql
CREATE TABLE tt_content (
    tx_myextension_link varchar(1024) DEFAULT '' NOT NULL
);
```

### 3. TypoScript rendering — setup.typoscript

```typoscript
# Add template path
lib.contentElement.templateRootPaths.200 = EXT:my_extension/Resources/Private/Templates/ContentElements/

# Register content element rendering
tt_content.myextension_teaser =< lib.contentElement
tt_content.myextension_teaser {
    templateName = Teaser

    dataProcessing {
        10 = files
        10 {
            references.fieldName = image
            as = images
        }
    }
}
```

`lib.contentElement` is defined by `fluid_styled_content` and provides the base FLUIDTEMPLATE setup.

### 4. Fluid template

```html
<!-- Resources/Private/Templates/ContentElements/Teaser.html -->
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">

<f:layout name="ContentElement" />

<f:section name="Main">
    <div class="teaser">
        <f:if condition="{data.header}">
            <h2>{data.header}</h2>
        </f:if>

        <f:if condition="{images}">
            <f:for each="{images}" as="image">
                <f:image image="{image}" maxWidth="600" class="teaser__image" loading="lazy" />
            </f:for>
        </f:if>

        <f:if condition="{data.bodytext}">
            <div class="teaser__body">
                <f:format.html>{data.bodytext}</f:format.html>
            </div>
        </f:if>

        <f:if condition="{data.tx_myextension_link}">
            <f:link.typolink parameter="{data.tx_myextension_link}" class="btn btn-primary">
                <f:translate key="ce.teaser.readmore" />
            </f:link.typolink>
        </f:if>
    </div>
</f:section>
</html>
```

Layout uses `<f:layout name="ContentElement" />` which wraps output in standard content element markup (anchor, layout class, frame class).

### 5. Icon registration — Configuration/Icons.php

```php
<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'my-extension-teaser' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:my_extension/Resources/Public/Icons/ContentElements/teaser.svg',
    ],
];
```

---

## Data Processors for Content Elements

Chain multiple processors:

```typoscript
tt_content.myextension_teamlist =< lib.contentElement
tt_content.myextension_teamlist {
    templateName = TeamList

    dataProcessing {
        10 = database-query
        10 {
            table = tx_myextension_domain_model_member
            pidInList.field = pages
            pidInList.override.field = pid
            as = members
            where.data = field:uid
        }

        # Process files for each member record
        20 = files
        20 {
            references.fieldName = image
            references.table = tx_myextension_domain_model_member
            as = memberImages
        }

        # FlexForm processing
        30 = flex-form
        30 {
            fieldName = pi_flexform
            as = flexformData
        }
    }
}
```

### Custom Data Processor

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

final class CustomProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly SomeDependency $dependency,
    ) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $as = $cObj->stdWrapValue('as', $processorConfiguration, 'customData');
        $processedData[$as] = $this->dependency->getItems();
        return $processedData;
    }
}
```

Register alias in Services.yaml:
```yaml
services:
  Vendor\MyExtension\DataProcessing\CustomProcessor:
    tags:
      - name: 'data.processor'
        identifier: 'my-custom'
```

Use in TypoScript: `40 = my-custom` or `40 = Vendor\MyExtension\DataProcessing\CustomProcessor`.

---

## Backend Preview for Custom CEs

### Via Page TSconfig (simple, no PHP)

```tsconfig
mod.web_layout.tt_content.preview.myextension_teaser =
    EXT:my_extension/Resources/Private/Templates/Preview/Teaser.html
```

```html
<!-- Resources/Private/Templates/Preview/Teaser.html -->
<div style="padding: 10px;">
    <strong>{record.header}</strong>
    <f:if condition="{record.bodytext}">
        <p>{record.bodytext -> f:format.crop(maxCharacters: 120)}</p>
    </f:if>
    <f:if condition="{record.tx_myextension_link}">
        <span class="badge badge-info">Has link</span>
    </f:if>
</div>
```

v14: Access via `{record}` (RecordInterface), not direct field variables.

### Via Event Listener (complex previews)

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;

#[AsEventListener(identifier: 'my-extension/teaser-preview')]
final class TeaserPreviewListener
{
    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content') return;
        if ($event->getRecord()['CType'] !== 'myextension_teaser') return;

        $record = $event->getRecord();
        $html = '<div><strong>' . htmlspecialchars($record['header']) . '</strong></div>';
        $event->setPreviewContent($html);
    }
}
```

---

## When to use which approach

| Scenario | Approach |
|----------|----------|
| Simple static content (text + image + link) | **ContentBlocks** |
| Content with nested items (slides, tabs, accordion) | **ContentBlocks** with Collection type |
| Content needing dynamic data (DB queries, API calls) | **Traditional** with data processors |
| Content with complex rendering logic | **Traditional** with custom data processor |
| Extbase plugin with models and repositories | **Extbase plugin** (not a CE) |
