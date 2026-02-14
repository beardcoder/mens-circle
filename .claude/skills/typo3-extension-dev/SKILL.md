---
name: typo3-extension-dev
description: >-
  Expert in TYPO3 extension development covering custom extensions, TCA configuration, Extbase,
  and TYPO3 APIs. Activates when creating extensions, working with domain models, repositories,
  backend modules, or plugins; or when the user mentions TYPO3 extension, Extbase, TCA, or domain models.
---

# TYPO3 Extension Developer

## When to Apply

Activate this skill when:

- Creating or modifying TYPO3 extensions
- Working with TCA (Table Configuration Array)
- Developing Extbase domain models and repositories
- Creating backend modules or frontend plugins
- Implementing TYPO3 Core APIs
- Setting up database tables and migrations

## Core Expertise Areas

### Extension Architecture

**Modern extension structure (TYPO3 v14):**
```
packages/my_extension/
  Classes/
    Command/           # CLI commands
    Controller/        # Extbase controllers
    Domain/
      Model/          # Domain models
      Repository/     # Repositories
    DataProcessing/   # DataProcessors
    Service/          # Business logic
    ViewHelpers/      # Custom ViewHelpers
  Configuration/
    Backend/
      Modules.php     # Backend module registration
    FlexForms/        # FlexForm definitions
    Icons.php         # Icon registration
    Sets/             # Site Set configuration
    TCA/              # Table definitions
    TypoScript/       # TypoScript
  Resources/
    Private/
      Fluid/          # Templates
      Language/       # Translations
      Frontend/       # Source assets
    Public/           # Compiled assets
  ext_emconf.php      # Extension metadata
  composer.json       # Composer configuration
```

**Composer integration:**
- PSR-4 autoloading configuration
- Dependency management
- Version constraints

### TCA Configuration

**Table definition** (`Configuration/TCA/tx_myext_domain_model_event.php`):
```php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_event',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,description',
        'iconfile' => 'EXT:my_extension/Resources/Public/Icons/event.svg',
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_event.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'date' => [
            'label' => 'LLL:EXT:my_extension/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_event.date',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'required' => true,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    title, date, description,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    hidden, starttime, endtime,
            ',
        ],
    ],
];
```

### Extbase Domain Models

**Domain model** (`Classes/Domain/Model/Event.php`):
```php
namespace Vendor\Extension\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Event extends AbstractEntity
{
    protected string $title = '';
    protected ?\DateTime $date = null;
    protected string $description = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }
}
```

**Repository** (`Classes/Domain/Repository/EventRepository.php`):
```php
namespace Vendor\Extension\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class EventRepository extends Repository
{
    public function findUpcoming(int $limit = 10): array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->greaterThan('date', new \DateTime())
        );
        $query->setOrderings(['date' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING]);
        $query->setLimit($limit);
        
        return $query->execute()->toArray();
    }
}
```

### Backend Modules

**Module registration** (`Configuration/Backend/Modules.php`):
```php
return [
    'menscircle_events' => [
        'parent' => 'web',
        'position' => ['after' => 'web_list'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/menscircle/events',
        'labels' => 'LLL:EXT:mens_circle/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'menscircle-events',
        'routes' => [
            '_default' => [
                'target' => \Vendor\MensCircle\Controller\Backend\EventController::class . '::handleRequest',
            ],
        ],
    ],
];
```

### CLI Commands

**Command class** (`Classes/Command/ImportCommand.php`):
```php
namespace Vendor\Extension\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Import data from external source');
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to import file');
        $this->addOption('truncate', 't', InputOption::VALUE_NONE, 'Truncate before import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $truncate = $input->getOption('truncate');
        
        // Implementation
        
        return Command::SUCCESS;
    }
}
```

**Registration** (`Configuration/Services.yaml`):
```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Vendor\Extension\:
    resource: '../Classes/*'

  Vendor\Extension\Command\ImportCommand:
    tags:
      - name: 'console.command'
        command: 'myext:import'
```

## Project Context (mens_circle)

**Extension:** `packages/mens_circle`

**Domain models:**
- `Event` - Event management
- `Registration` - Event registrations
- `Participant` - Participant data
- `NewsletterSubscription` - Newsletter subscriptions
- `Testimonial` - User testimonials

**Key features:**
- Import command: `menscircle:import:laravel-sql`
- Backend newsletter module
- Frontend event display plugin
- Registration workflow

**Database approach:**
- Uses Doctrine DBAL 4
- Schema-aware insert/update (checks columns exist)
- Parameter types from `Doctrine\DBAL\ParameterType`

## Best Practices

### Extension Development

**Code quality:**
- Follow TYPO3 coding standards
- Use PHP 8.3+ features (project uses 8.5)
- Implement proper type declarations
- Use dependency injection (autowiring)

**Architecture:**
- Domain-driven design with Extbase
- Repository pattern for data access
- Service classes for business logic
- DataProcessors for frontend data preparation

**Security:**
- Validate and sanitize all input
- Use prepared statements for queries
- Implement proper access controls
- Follow TYPO3 security guidelines

### Database Operations

**Use Extbase repositories:**
```php
// Good - Uses repository
$events = $this->eventRepository->findUpcoming();

// Avoid - Raw DBAL unless necessary
$connection->select('*', 'tx_myext_event', ['hidden' => 0]);
```

**DBAL 4 specifics:**
```php
use Doctrine\DBAL\ParameterType;

// Use ParameterType, not PDO constants
$qb->setParameter('uid', $uid, ParameterType::INTEGER);
```

### Dependency Injection

**Constructor injection (autowired):**
```php
class MyService
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly LoggerInterface $logger
    ) {}
}
```

**Services.yaml configuration:**
```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Vendor\Extension\:
    resource: '../Classes/*'
```

### Logging

**Use PSR-3 LoggerInterface:**
```php
class MyService
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function doSomething(): void
    {
        $this->logger->info('Action performed', ['context' => 'value']);
    }
}
```

## Common Tasks

### Creating New Domain Model

1. Create model class in `Classes/Domain/Model/`
2. Create repository in `Classes/Domain/Repository/`
3. Create TCA in `Configuration/TCA/`
4. Create database migration if needed
5. Register in `ext_tables.sql` or use migrations
6. Clear caches: `ddev exec vendor/bin/typo3 cache:flush`

### Adding Backend Module

1. Define in `Configuration/Backend/Modules.php`
2. Create controller in `Classes/Controller/Backend/`
3. Create templates in `Resources/Private/Fluid/Backend/`
4. Register icon in `Configuration/Icons.php`
5. Add translations in `Resources/Private/Language/`

### Creating CLI Command

1. Create command class in `Classes/Command/`
2. Register in `Configuration/Services.yaml`
3. Test: `ddev exec vendor/bin/typo3 myext:commandname`

## Testing

**Unit tests:**
- Test domain models and business logic
- Mock dependencies
- Use PHPUnit or Pest

**Functional tests:**
- Test database operations
- Test full workflows
- Use TYPO3 testing framework

## Tools Used

- **View**: Examine extension structure and code
- **Grep**: Find patterns and implementations
- **Bash**: Execute TYPO3 commands
- **Edit/Create**: Modify extension files

## Related Skills

- **typo3-architect**: For extension architecture design
- **typo3-content-blocks**: For content element integration
- **php-modernizer**: For modernizing extension code
- **pest-testing**: For testing extensions
