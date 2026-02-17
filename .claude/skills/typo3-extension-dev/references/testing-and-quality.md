# Testing & Code Quality for TYPO3 v14 Extensions

Best practices derived from the official TYPO3 `tea` extension (TYPO3BestPractices/tea) and TYPO3 coding guidelines.

## Table of Contents

1. [Testing Setup](#testing-setup)
2. [Unit Tests](#unit-tests)
3. [Functional Tests](#functional-tests)
4. [PHPStan Configuration](#phpstan-configuration)
5. [PHP-CS-Fixer](#php-cs-fixer)
6. [Rector](#rector)
7. [Additional Linters](#additional-linters)
8. [Composer Scripts](#composer-scripts)
9. [GitHub Actions CI](#github-actions-ci)

---

## Testing Setup

### composer.json dev dependencies

```json
{
    "require-dev": {
        "friendsofphp/php-cs-fixer": "^3.50",
        "helmich/typo3-typoscript-lint": "^3.3",
        "phpstan/phpstan": "^1.12 || ^2.0",
        "phpstan/phpstan-phpunit": "^1.4 || ^2.0",
        "phpstan/phpstan-strict-rules": "^1.6 || ^2.0",
        "phpunit/phpunit": "^10.5",
        "saschaegerer/phpstan-typo3": "^1.10 || ^2.0",
        "ssch/typo3-rector": "^2.15 || ^3.0",
        "typo3/coding-standards": "^0.8",
        "typo3/testing-framework": "^8.3"
    },
    "autoload-dev": {
        "psr-4": {
            "Vendor\\MyExtension\\Tests\\": "Tests/"
        }
    },
    "config": {
        "bin-dir": ".Build/bin",
        "vendor-dir": ".Build/vendor"
    },
    "extra": {
        "typo3/cms": {
            "web-dir": ".Build/public"
        }
    }
}
```

### PHPUnit Configuration

#### Build/phpunit/UnitTests.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.5/phpunit.xsd"
    bootstrap="../../.Build/vendor/typo3/testing-framework/Resources/Core/Build/UnitTestsBootstrap.php"
    cacheResult="false"
    colors="true"
    failOnRisky="true"
    failOnWarning="true"
>
    <php>
        <ini name="display_errors" value="1"/>
        <env name="TYPO3_CONTEXT" value="Testing"/>
    </php>
    <source>
        <include>
            <directory>../../Classes</directory>
        </include>
    </source>
    <testsuites>
        <testsuite name="Unit">
            <directory>../../Tests/Unit</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

#### Build/phpunit/FunctionalTests.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.5/phpunit.xsd"
    backupGlobals="true"
    bootstrap="../../.Build/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTestsBootstrap.php"
    cacheResult="false"
    colors="true"
    failOnRisky="true"
    failOnWarning="true"
>
    <php>
        <ini name="display_errors" value="1"/>
        <env name="TYPO3_CONTEXT" value="Testing"/>
    </php>
    <source>
        <include>
            <directory>../../Classes</directory>
        </include>
    </source>
    <testsuites>
        <testsuite name="Functional">
            <directory>../../Tests/Functional</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## Unit Tests

Unit tests extend `TYPO3\TestingFramework\Core\Unit\UnitTestCase`. They test single classes in isolation. Use PHP 8 attributes for test configuration.

### Model Test Example

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Vendor\MyExtension\Domain\Model\Item;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Item::class)]
final class ItemTest extends UnitTestCase
{
    private Item $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Item();
    }

    #[Test]
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    #[Test]
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    #[Test]
    public function setTitleSetsTitle(): void
    {
        $value = 'Earl Grey';
        $this->subject->setTitle($value);
        self::assertSame($value, $this->subject->getTitle());
    }

    #[Test]
    public function getImageInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getImage());
    }
}
```

### Key patterns

- Use `#[Test]` attribute instead of `@test` annotation or `test` prefix
- Use `#[CoversClass(ClassName::class)]` for coverage mapping
- Mark test classes `final`
- Use `self::assert*()` instead of `$this->assert*()`
- Name tests: `methodNameDescriptionOfBehavior`

---

## Functional Tests

Functional tests extend `TYPO3\TestingFramework\Core\Functional\FunctionalTestCase`. They test with a real TYPO3 instance and database.

### Repository Test

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Tests\Functional\Domain\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Vendor\MyExtension\Domain\Model\Item;
use Vendor\MyExtension\Domain\Repository\ItemRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ItemRepository::class)]
final class ItemRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'vendor/my-extension',
    ];

    private ItemRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->get(ItemRepository::class);
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Items.csv');
    }

    #[Test]
    public function findAllReturnsAllItems(): void
    {
        $result = $this->subject->findAll();
        self::assertCount(2, $result);
    }

    #[Test]
    public function findBySearchQueryFindsMatchingItems(): void
    {
        $result = $this->subject->findBySearchQuery('Earl');
        self::assertCount(1, $result);
        self::assertSame('Earl Grey', $result->getFirst()->getTitle());
    }
}
```

### CSV Fixture Files

```csv
"tx_myext_domain_model_item"
,"uid","pid","title","description","hidden","deleted"
,1,1,"Earl Grey","A classic tea",0,0
,2,1,"Green Tea","Refreshing",0,0
```

### Frontend Controller Test

```php
<?php

declare(strict_types=1);

namespace Vendor\MyExtension\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Vendor\MyExtension\Controller\ItemController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

#[CoversClass(ItemController::class)]
final class ItemControllerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'vendor/my-extension',
    ];

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ContentElements.csv');
        $this->setUpFrontendRootPage(1, [
            'EXT:my_extension/Configuration/TypoScript/setup.typoscript',
        ]);
    }

    #[Test]
    public function indexActionRendersItemList(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Items.csv');
        $request = (new InternalRequest())->withPageId(1);
        $html = (string)$this->executeFrontendSubRequest($request)->getBody();
        self::assertStringContainsString('Earl Grey', $html);
    }

    #[Test]
    public function showActionReturns404ForMissingItem(): void
    {
        $request = (new InternalRequest())->withPageId(1)
            ->withQueryParameters(['tx_myext_show[item]' => 999]);
        $response = $this->executeFrontendSubRequest($request);
        self::assertSame(404, $response->getStatusCode());
    }
}
```

---

## PHPStan Configuration

### Build/phpstan/phpstan.neon

```neon
includes:
  - phpstan-baseline.neon

parameters:
  level: 9

  paths:
    - ../../Classes
    - ../../Configuration
    - ../../Tests

  type_coverage:
    return_type: 100
    param_type: 100
    property_type: 95

  cognitive_complexity:
    class: 10
    function: 5

  disallowedFunctionCalls:
    -
      function: ['var_dump()', 'debug()', 'xdebug_break()']
      message: 'Use logging instead.'
    -
      function: 'header()'
      message: 'Use PSR-7 API instead.'

  disallowedStaticCalls:
    -
      method: ['TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump()']
      message: 'Use logging instead.'

  disallowedSuperglobals:
    -
      superglobal: ['$_GET', '$_POST', '$_FILES', '$_SERVER']
      message: 'Use PSR-7 API instead.'
```

Create empty baseline: `Build/phpstan/phpstan-baseline.neon` with just `parameters: []`

---

## PHP-CS-Fixer

### Build/php-cs-fixer/config.php

```php
<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config->setParallelConfig(ParallelConfigFactory::detect());
$config->getFinder()->in('Classes')->in('Configuration')->in('Tests');

return $config;
```

Uses TYPO3 coding standards which enforce PSR-12 plus TYPO3-specific rules.

---

## Rector

### Build/rector/config.php

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../../Classes',
        __DIR__ . '/../../Configuration',
        __DIR__ . '/../../Tests',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        Typo3LevelSetList::UP_TO_TYPO3_14,
    ]);
```

---

## Additional Linters

### TypoScript Lint — Build/typoscript-lint/config.yml

```yaml
paths:
  - Configuration/TypoScript

sniffs:
  - class: Indentation
    parameters:
      useSpaces: true
      indentPerLevel: 4
  - class: DeadCode
  - class: OperatorWhitespace
  - class: RepeatingRValue
  - class: DuplicateAssignment
  - class: NestingConsistency
  - class: EmptySection
```

### XLIFF Lint

Lint XLF language files for valid XML and correct structure.

---

## Composer Scripts

Add these to your `composer.json` for a standard QA pipeline:

```json
{
    "scripts": {
        "check:php:lint": "parallel-lint *.php Classes Configuration Tests",
        "check:php:cs-fixer": "php-cs-fixer fix --config ./Build/php-cs-fixer/config.php -v --dry-run --diff",
        "check:php:stan": "phpstan --no-progress --configuration=Build/phpstan/phpstan.neon",
        "check:php:rector": "rector --dry-run --config=./Build/rector/config.php",
        "check:typoscript:lint": "typoscript-lint -c Build/typoscript-lint/config.yml --ansi -n --fail-on-warnings Configuration/TypoScript",
        "check:tests:unit": "phpunit -c Build/phpunit/UnitTests.xml",
        "check:tests:functional": [
            "mkdir -p .Build/public/typo3temp/var/tests",
            "phpunit -c Build/phpunit/FunctionalTests.xml"
        ],
        "fix:php:cs-fixer": "php-cs-fixer fix --config ./Build/php-cs-fixer/config.php",
        "fix:php:rector": "rector --config=./Build/rector/config.php"
    }
}
```

---

## GitHub Actions CI

### .github/workflows/ci.yml

```yaml
name: CI
on:
  push:
    branches: [main]
  pull_request:

jobs:
  php-lint:
    name: PHP Lint
    runs-on: ubuntu-24.04
    strategy:
      matrix:
        php-version: ['8.2', '8.3', '8.4']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
      - run: composer install --no-progress
      - run: composer check:php:lint

  code-quality:
    name: Code Quality
    runs-on: ubuntu-24.04
    strategy:
      matrix:
        command: [cs-fixer, stan, rector]
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install --no-progress
      - run: composer check:php:${{ matrix.command }}

  unit-tests:
    name: Unit Tests
    runs-on: ubuntu-24.04
    needs: php-lint
    strategy:
      matrix:
        php-version: ['8.2', '8.3', '8.4']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
      - run: composer install --no-progress
      - run: composer check:tests:unit

  functional-tests:
    name: Functional Tests
    runs-on: ubuntu-24.04
    needs: php-lint
    strategy:
      matrix:
        php-version: ['8.2', '8.3']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
      - run: composer install --no-progress
      - run: composer check:tests:functional
```

---

## TYPO3 Coding Guidelines Summary

1. **Strict types** in every file
2. **Final classes** for controllers, event listeners, tests, middleware, data processors
3. **Readonly constructor properties** for injected services
4. **No public properties** — use getters/setters (exception: domain models may use protected)
5. **Return types on all methods** — 100% return type coverage target
6. **Parameter types on all methods** — 100% param type coverage target
7. **PHPStan level 9** — maximum strictness
8. **No var_dump / debug** — use PSR-3 logging
9. **PSR-7 for request/response** — no superglobals
10. **PHPUnit attributes** — `#[Test]`, `#[CoversClass]`, not annotations
