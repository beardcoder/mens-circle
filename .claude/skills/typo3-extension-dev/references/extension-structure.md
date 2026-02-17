# Extension Structure Reference for TYPO3 v14

Based on the official TYPO3 Core API Reference, the site_package tutorial, and the tea best-practices extension.

## composer.json

```json
{
    "name": "vendor/my-extension",
    "type": "typo3-cms-extension",
    "description": "My TYPO3 extension",
    "license": "GPL-2.0-or-later",
    "require": {
        "php": "~8.2.0 || ~8.3.0 || ~8.4.0",
        "typo3/cms-core": "^14.0",
        "typo3/cms-extbase": "^14.0",
        "typo3/cms-fluid": "^14.0",
        "typo3/cms-frontend": "^14.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\MyExtension\\": "Classes/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Vendor\\MyExtension\\Tests\\": "Tests/"
        }
    },
    "extra": {
        "typo3/cms": {
            "extension-key": "my_extension"
        }
    }
}
```

For sitepackages add: `"typo3/cms-fluid-styled-content": "^14.0"` and optionally `"friendsoftypo3/content-blocks"`.

---

## ext_emconf.php

```php
<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'My Extension',
    'description' => 'Description of the extension.',
    'category' => 'plugin',   // or: templates, module, fe, be, misc, services
    'state' => 'stable',
    'author' => 'Author Name',
    'author_email' => 'author@example.com',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
    ],
];
```

---

## ext_localconf.php (Plugin Registration)

### v14 style — register as CType (recommended)

```php
<?php

declare(strict_types=1);

use Vendor\MyExtension\Controller\ItemController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die('Access denied.');

// Register as native content element (CType)
ExtensionUtility::configurePlugin(
    'MyExtension',      // Extbase extension name (no vendor)
    'List',             // Plugin name
    [ItemController::class => 'list,show'],    // All actions
    [ItemController::class => ''],             // Non-cacheable actions
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,  // v14: registers as CType
);
```

### Legacy style — register as list_type (still works)

```php
ExtensionUtility::configurePlugin(
    'MyExtension',
    'List',
    [ItemController::class => 'list,show'],
    [ItemController::class => ''],
    // No PLUGIN_TYPE_CONTENT_ELEMENT → uses list_type
);
```

---

## Configuration/TCA/Overrides/tt_content.php (Plugin Selection in BE)

### v14 Extbase plugin (register as selectable CType)

```php
<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::registerPlugin(
    'MyExtension',                          // Extbase extension name
    'List',                                 // Plugin name
    'LLL:EXT:my_extension/Resources/Private/Language/locallang.xlf:plugin.list.title',
    'my-extension-plugin-icon',             // Icon identifier
    'plugins',                              // Group in New CE wizard
    'LLL:EXT:my_extension/Resources/Private/Language/locallang.xlf:plugin.list.description',
    'FILE:EXT:my_extension/Configuration/FlexForms/List.xml',  // v14: FlexForm as 7th argument
);
```

### Custom content element (non-Extbase)

```php
<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$key = 'myextension_teaser';

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => 'My Teaser',
        'value' => $key,
        'group' => 'default',
    ],
);

// v13.3+: system fields auto-added; only specify custom fields
$GLOBALS['TCA']['tt_content']['types'][$key] = [
    'showitem' => '
        --palette--;;headers,
        bodytext, image,
    ',
    'columnsOverrides' => [
        'bodytext' => ['config' => ['enableRichtext' => true]],
    ],
];
```

---

## Configuration/Services.yaml

Standard dependency injection config:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Vendor\MyExtension\:
    resource: '../Classes/*'
    exclude: '../Classes/Domain/Model/*'
```

### Alternative: Services.php (tea extension style, for more flexibility)

```php
<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Vendor\MyExtension\Command\ImportCommand;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator
        ->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->load('Vendor\\MyExtension\\', '../Classes/*')
        ->exclude('../Classes/Domain/Model/*');

    // Register console command via tag
    $services
        ->set(ImportCommand::class)
        ->tag('console.command', [
            'command' => 'myext:import',
            'description' => 'Import data',
        ]);
};
```

### Register data processor alias

```yaml
services:
  Vendor\MyExtension\DataProcessing\MyProcessor:
    tags:
      - name: 'data.processor'
        identifier: 'my-custom-processor'
```

---

## Configuration/Sets/ (Site Sets)

```yaml
# Configuration/Sets/MyExtension/config.yaml
name: vendor/my-extension
label: My Extension
dependencies:
  - typo3/fluid-styled-content
```

```yaml
# Configuration/Sets/MyExtension/settings.definitions.yaml
categories:
  MyExt:
    label: My Extension
  MyExt.display:
    label: Display
    parent: MyExt

settings:
  MyExt.itemsPerPage:
    label: Items per page
    category: MyExt.display
    type: int
    default: 10
  MyExt.logo:
    label: Logo
    category: MyExt.display
    type: string
    default: 'EXT:my_extension/Resources/Public/Images/logo.svg'
```

Setup and constants files at same level: `setup.typoscript`, `constants.typoscript`, `page.tsconfig`.

`page.tsconfig` supports `@import`:
```tsconfig
@import './PageTsConfig/'
@import './PageTsConfig/BackendLayouts/'
```

---

## Configuration/TCA/ (Table Configuration)

### New table: Configuration/TCA/tx_myext_domain_model_item.php

```php
<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:tx_myext_item',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'default_sortby' => 'title ASC',
        'iconfile' => 'EXT:my_extension/Resources/Public/Icons/Record.svg',
        'searchFields' => 'title,description',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;General,
                    title, slug, description, image,
                --div--;Access,
                    --palette--;;hidden,
                    --palette--;;access,
            ',
        ],
    ],
    'palettes' => [
        'hidden' => ['showitem' => 'hidden'],
        'access' => ['showitem' => 'starttime, endtime, --linebreak--, fe_group'],
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => ['type' => 'input', 'size' => 50, 'max' => 255, 'required' => true, 'eval' => 'trim'],
        ],
        'description' => [
            'label' => 'Description',
            'config' => ['type' => 'text', 'rows' => 8, 'enableRichtext' => true],
        ],
        'image' => [
            'label' => 'Image',
            'config' => ['type' => 'file', 'maxitems' => 1, 'allowed' => 'common-image-types'],
        ],
        // Standard fields: hidden, starttime, endtime, fe_group, sys_language_uid, l10n_parent etc.
        // are auto-defined when referenced in ctrl
    ],
];
```

---

## Configuration/Icons.php

```php
<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'my-extension-record' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:my_extension/Resources/Public/Icons/Record.svg',
    ],
    'my-extension-plugin' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:my_extension/Resources/Public/Icons/Extension.svg',
    ],
];
```

---

## ext_tables.sql

Only needed for fields NOT auto-generated by TCA. Since v13, most types auto-generate columns.

```sql
-- Custom MM table
CREATE TABLE tx_myext_item_tag_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

-- Custom column specs (only if defaults don't fit)
CREATE TABLE tx_myext_domain_model_item (
    slug varchar(2048) DEFAULT '' NOT NULL
);
```

---

## Configuration/FlexForms/

FlexForm XML for Extbase plugin settings (v13+ — no TCEforms wrapper!):

```xml
<T3DataStructure>
    <sheets>
        <sDEF>
            <ROOT>
                <sheetTitle>Settings</sheetTitle>
                <type>array</type>
                <el>
                    <settings.itemsPerPage>
                        <label>Items per page</label>
                        <config>
                            <type>number</type>
                            <default>10</default>
                            <range>
                                <lower>1</lower>
                                <upper>100</upper>
                            </range>
                        </config>
                    </settings.itemsPerPage>
                    <settings.displayMode>
                        <label>Display mode</label>
                        <config>
                            <type>select</type>
                            <renderType>selectSingle</renderType>
                            <items>
                                <numIndex index="0">
                                    <label>List</label>
                                    <value>list</value>
                                </numIndex>
                                <numIndex index="1">
                                    <label>Grid</label>
                                    <value>grid</value>
                                </numIndex>
                            </items>
                        </config>
                    </settings.displayMode>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
```

Fields prefixed with `settings.` are automatically available as `$this->settings['itemsPerPage']` in controllers and `{settings.itemsPerPage}` in Fluid.

---

## Configuration/Backend/Modules.php

```php
<?php

use Vendor\MyExtension\Controller\Backend\AdminController;

return [
    'my_module' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'iconIdentifier' => 'my-extension-module',
        'labels' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MyExtension',
        'controllerActions' => [
            AdminController::class => ['index', 'detail'],
        ],
    ],
];
```

---

## PSR-14 Event Listeners

### Via PHP attribute (recommended, v13+)

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Mail\Event\AfterMailerSentMessageEvent;

#[AsEventListener(identifier: 'my-extension/after-mail-sent')]
final readonly class AfterMailSentListener
{
    public function __invoke(AfterMailerSentMessageEvent $event): void
    {
        // Handle event
    }
}
```

### Via Services.yaml (alternative)

```yaml
services:
  Vendor\MyExtension\EventListener\AfterMailSentListener:
    tags:
      - name: event.listener
        identifier: 'my-extension/after-mail-sent'
        event: TYPO3\CMS\Core\Mail\Event\AfterMailerSentMessageEvent
```

### Custom event class

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Event;

final class ItemCreatedEvent
{
    public function __construct(
        private string $title,
        private readonly int $uid,
    ) {}

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getUid(): int { return $this->uid; }
}
```

---

## Console Commands

### Via PHP attribute (recommended)

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'myext:import',
    description: 'Import items from external source',
)]
final class ImportCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('source', InputArgument::REQUIRED, 'Source URL');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');
        $io->success('Imported from ' . $source);
        return Command::SUCCESS;
    }
}
```

---

## Middleware

### Via PHP attribute (v13+)

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Attribute\AsMiddleware;

#[AsMiddleware(
    identifier: 'my-extension/custom-header',
    after: 'typo3/cms-frontend/site',
    type: AsMiddleware::TYPE_FRONTEND,
)]
final class CustomHeaderMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        return $response->withHeader('X-Custom', 'value');
    }
}
```

### Via Configuration/RequestMiddlewares.php (alternative)

```php
<?php

return [
    'frontend' => [
        'vendor/my-extension/custom-header' => [
            'target' => \Vendor\MyExtension\Middleware\CustomHeaderMiddleware::class,
            'after' => ['typo3/cms-frontend/site'],
        ],
    ],
];
```

---

## Configuration/Extbase/Persistence/Classes.php

Map domain model properties to database field names:

```php
<?php

declare(strict_types=1);

use Vendor\MyExtension\Domain\Model\Item;

return [
    Item::class => [
        'tableName' => 'tx_myext_domain_model_item',  // only if table name differs
        'properties' => [
            'ownerUid' => ['fieldName' => 'owner'],    // property → DB field
        ],
    ],
];
```

---

## Language Files (XLIFF)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="messages" date="2024-01-01T00:00:00Z">
        <header/>
        <body>
            <trans-unit id="plugin.list.title" resname="plugin.list.title">
                <source>Item List</source>
            </trans-unit>
            <trans-unit id="list.header" resname="list.header">
                <source>Items</source>
            </trans-unit>
        </body>
    </file>
</xliff>
```

Translation file: `de.locallang.xlf` with `target-language="de"` and `<target>` elements.

---

## Backend Preview (v14)

### Via Page TSconfig (recommended, no PHP needed)

```tsconfig
mod.web_layout.tt_content.preview.myextension_teaser =
    EXT:my_extension/Resources/Private/Templates/Preview/Teaser.html
```

Template receives `{record}` (RecordInterface, v14):

```html
<h3>{record.header}</h3>
<p>{record.bodytext -> f:format.crop(maxCharacters: 100)}</p>
```

### Via event listener (for complex logic)

Listen to `PageContentPreviewRenderingEvent`.
