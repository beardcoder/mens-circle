# TCA Reference for TYPO3 v14

Complete reference for the TYPO3 Configuration Array — defining database tables, fields, and backend forms.

## Table of Contents

1. [ctrl Section](#ctrl-section)
2. [Column Types](#column-types)
3. [Types Section](#types-section)
4. [Palettes](#palettes)
5. [Auto-generated DB Fields](#auto-generated-db-fields)

---

## ctrl Section

The `ctrl` section defines table-level metadata:

```php
'ctrl' => [
    'title' => 'LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tx_myext_item',
    'label' => 'title',                    // Field used as record label
    'label_alt' => 'subtitle',             // Alternative label fields
    'label_alt_force' => true,             // Always show alt labels
    'tstamp' => 'tstamp',                  // Auto-updated timestamp
    'crdate' => 'crdate',                  // Creation date
    'delete' => 'deleted',                 // Soft-delete flag
    'sortby' => 'sorting',                 // Manual sorting field
    'default_sortby' => 'title ASC',       // Default sort (if no sortby)
    'languageField' => 'sys_language_uid', // Localization
    'transOrigPointerField' => 'l10n_parent',
    'transOrigDiffSourceField' => 'l10n_diffsource',
    'translationSource' => 'l10n_source',
    'enablecolumns' => [
        'disabled' => 'hidden',
        'starttime' => 'starttime',
        'endtime' => 'endtime',
        'fe_group' => 'fe_group',
    ],
    'searchFields' => 'title,description',
    'iconfile' => 'EXT:my_ext/Resources/Public/Icons/Record.svg',
    'security' => [
        'ignorePageTypeRestriction' => true,  // Allow on any page type
    ],
    'typeicon_classes' => [                // Per-type icons
        'default' => 'my-ext-record',
    ],
],
```

Standard fields (uid, pid, tstamp, crdate, deleted, hidden, starttime, endtime, sorting, sys_language_uid, l10n_parent, l10n_diffsource, fe_group) are auto-created by TYPO3 when referenced in ctrl — no ext_tables.sql needed for these.

---

## Column Types

TYPO3 v14 provides these TCA column types. Most auto-generate their database columns — only define `ext_tables.sql` if you need custom column specs.

### type: input

Simple text input. Auto-generates VARCHAR or TEXT.

```php
'title' => [
    'label' => 'Title',
    'config' => [
        'type' => 'input',
        'size' => 50,
        'max' => 255,
        'required' => true,
        'placeholder' => 'Enter title...',
        'eval' => 'trim',
    ],
],
```

### type: text

Multi-line textarea, optionally with RTE.

```php
'description' => [
    'label' => 'Description',
    'config' => [
        'type' => 'text',
        'rows' => 10,
        'cols' => 40,
        'enableRichtext' => true,       // Enable CKEditor
        'richtextConfiguration' => 'default', // RTE preset
    ],
],
```

### type: number

```php
'amount' => [
    'label' => 'Amount',
    'config' => [
        'type' => 'number',
        'format' => 'integer',   // or 'decimal'
        'range' => ['lower' => 0, 'upper' => 1000],
        'default' => 0,
        'required' => true,
    ],
],
```

### type: email

```php
'email' => [
    'label' => 'Email',
    'config' => [
        'type' => 'email',
    ],
],
```

### type: link

```php
'website' => [
    'label' => 'Website',
    'config' => [
        'type' => 'link',
        'allowedTypes' => ['page', 'url', 'file', 'folder', 'email', 'telephone', 'record'],
        // Restrict: 'allowedTypes' => ['url', 'page'],
    ],
],
```

### type: color

```php
'brand_color' => [
    'label' => 'Brand Color',
    'config' => [
        'type' => 'color',
    ],
],
```

### type: datetime

```php
'publish_date' => [
    'label' => 'Publish Date',
    'config' => [
        'type' => 'datetime',
        'format' => 'date',     // 'date', 'time', 'datetime', 'timesec'
        'dbType' => 'date',     // Store as SQL DATE instead of int
        'required' => true,
    ],
],
```

### type: select

```php
// Static items
'status' => [
    'label' => 'Status',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['label' => 'Draft', 'value' => 'draft'],
            ['label' => 'Published', 'value' => 'published'],
            ['label' => 'Archived', 'value' => 'archived'],
        ],
        'default' => 'draft',
    ],
],

// Foreign table relation (n:1)
'category' => [
    'label' => 'Category',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'foreign_table' => 'tx_myext_domain_model_category',
        'foreign_table_where' => 'ORDER BY tx_myext_domain_model_category.title',
        'minitems' => 0,
        'maxitems' => 1,
    ],
],

// Multiple selection (n:m)
'tags' => [
    'label' => 'Tags',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'foreign_table' => 'tx_myext_domain_model_tag',
        'MM' => 'tx_myext_item_tag_mm',
        'minitems' => 0,
        'maxitems' => 99,
    ],
],

// Checkboxes
'options' => [
    'label' => 'Options',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectCheckBox',
        'items' => [
            ['label' => 'Option A', 'value' => 'a'],
            ['label' => 'Option B', 'value' => 'b'],
        ],
    ],
],

// Tree (hierarchical)
'category_tree' => [
    'label' => 'Categories',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectTree',
        'foreign_table' => 'sys_category',
        'foreign_table_where' => 'ORDER BY sys_category.title ASC',
        'treeConfig' => [
            'parentField' => 'parent',
            'appearance' => [
                'showHeader' => true,
                'expandAll' => true,
            ],
        ],
        'minitems' => 0,
        'maxitems' => 20,
    ],
],
```

renderType options: `selectSingle`, `selectSingleBox`, `selectCheckBox`, `selectMultipleSideBySide`, `selectTree`

### type: category

Shorthand for sys_category relations:

```php
'categories' => [
    'config' => [
        'type' => 'category',
    ],
],
```

### type: check

```php
'featured' => [
    'label' => 'Featured',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',  // or 'checkboxLabeledToggle'
        'default' => 0,
    ],
],
```

### type: radio

```php
'alignment' => [
    'label' => 'Alignment',
    'config' => [
        'type' => 'radio',
        'items' => [
            ['label' => 'Left', 'value' => 'left'],
            ['label' => 'Center', 'value' => 'center'],
            ['label' => 'Right', 'value' => 'right'],
        ],
        'default' => 'left',
    ],
],
```

### type: file

For FAL file references. Auto-generates int field.

```php
'image' => [
    'label' => 'Image',
    'config' => [
        'type' => 'file',
        'maxitems' => 5,
        'allowed' => 'common-image-types',  // or 'png,jpg,svg'
    ],
],
'documents' => [
    'label' => 'Documents',
    'config' => [
        'type' => 'file',
        'maxitems' => 10,
        'allowed' => 'pdf,doc,docx',
    ],
],
```

### type: inline (IRRE)

For 1:n child records:

```php
'items' => [
    'label' => 'Items',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'tx_myext_domain_model_item',
        'foreign_field' => 'parent_uid',
        'foreign_sortby' => 'sorting',
        'maxitems' => 99,
        'appearance' => [
            'collapseAll' => true,
            'expandSingle' => true,
            'useSortable' => true,
            'showSynchronizationLink' => false,
            'showAllLocalizationLink' => true,
            'showPossibleLocalizationRecords' => true,
            'levelLinksPosition' => 'both',
        ],
    ],
],
```

### type: group

For relations to records from one or more tables:

```php
'related_pages' => [
    'label' => 'Related Pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'maxitems' => 5,
        'suggestOptions' => [
            'default' => ['additionalSearchFields' => 'nav_title, url'],
        ],
    ],
],
```

### type: slug

```php
'slug' => [
    'label' => 'URL Segment',
    'config' => [
        'type' => 'slug',
        'generatorOptions' => [
            'fields' => ['title'],
            'fieldSeparator' => '-',
            'replacements' => ['/' => '-'],
        ],
        'fallbackCharacter' => '-',
        'eval' => 'uniqueInSite',    // or 'unique', 'uniqueInPid'
    ],
],
```

### type: flex

For FlexForm fields:

```php
'pi_flexform' => [
    'label' => 'Plugin Settings',
    'config' => [
        'type' => 'flex',
        'ds_pointerField' => 'list_type',
        'ds' => [
            'default' => 'FILE:EXT:my_ext/Configuration/FlexForms/Default.xml',
            '*,myext_list' => 'FILE:EXT:my_ext/Configuration/FlexForms/List.xml',
        ],
    ],
],
```

### type: json

```php
'configuration' => [
    'label' => 'Configuration (JSON)',
    'config' => [
        'type' => 'json',
    ],
],
```

### type: uuid

```php
'uuid' => [
    'label' => 'UUID',
    'config' => [
        'type' => 'uuid',
        'version' => 4,   // UUID v4
    ],
],
```

### type: password

```php
'api_key' => [
    'label' => 'API Key',
    'config' => [
        'type' => 'password',
        'hashed' => false,   // true for hashed storage
    ],
],
```

### type: country

```php
'country' => [
    'label' => 'Country',
    'config' => [
        'type' => 'country',
    ],
],
```

### type: language, passthrough, none, user, folder, imageManipulation

These are specialized types. See official docs for details. `language` is for sys_language_uid, `passthrough` stores values without rendering a field, `none` is display-only.

---

## Types Section

Defines which fields show for which record type:

```php
'types' => [
    '1' => [
        'showitem' => '
            --div--;General,
                --palette--;;general,
                title, slug, description, image,
            --div--;Relations,
                categories, tags,
            --div--;Access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;Language,
                --palette--;;language,
        ',
        'columnsOverrides' => [
            'description' => ['config' => ['enableRichtext' => true]],
        ],
    ],
],
```

**v13.3+**: System fields (hidden, starttime, endtime, fe_group, language) are auto-added to content types (tt_content). You only need to specify your custom fields.

---

## Palettes

Group related fields on one line:

```php
'palettes' => [
    'general' => [
        'label' => 'General',
        'showitem' => 'title, --linebreak--, slug',
    ],
],
```

---

## Auto-generated DB Fields

Since TYPO3 v13, most TCA types auto-generate their database columns. You only need `ext_tables.sql` for:

- Custom VARCHAR lengths beyond defaults
- Custom column types not matching TCA type defaults
- MM relation tables (if not using existing ones)
- Additional indexes

Standard ctrl fields are always auto-created.
