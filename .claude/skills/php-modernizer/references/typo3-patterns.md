# TYPO3-Specific Optimization Patterns

Framework-specific improvements for TYPO3 projects. Apply these when `typo3/cms-*` packages are detected in composer.json.

## 1. Modern Extbase Patterns

### Pattern: Constructor Injection over Inject Annotations

**Before:**
```php
class UserController extends ActionController
{
    /**
     * @var UserRepository
     */
    protected $userRepository;
    
    /**
     * @param UserRepository $userRepository
     */
    public function injectUserRepository(UserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }
}
```

**After:**
```php
class UserController extends ActionController
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}
}
```

### Pattern: Typed Repository Methods

**Before:**
```php
class UserRepository extends Repository
{
    /**
     * @param int $uid
     * @return User|null
     */
    public function findByUid($uid)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('uid', $uid));
        return $query->execute()->getFirst();
    }
}
```

**After:**
```php
class UserRepository extends Repository
{
    public function findByUid(int $uid): ?User
    {
        $query = $this->createQuery();
        return $query->matching($query->equals('uid', $uid))
            ->execute()
            ->getFirst();
    }
}
```

## 2. QueryBuilder Modernization

### Pattern: Modern Query Building

**Before:**
```php
$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
    ->getQueryBuilderForTable('tx_myext_domain_model_user');

$result = $queryBuilder
    ->select('*')
    ->from('tx_myext_domain_model_user')
    ->where(
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)),
        $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
    )
    ->execute()
    ->fetchAll();
```

**After:**
```php
$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
    ->getQueryBuilderForTable('tx_myext_domain_model_user');

$result = $queryBuilder
    ->select('*')
    ->from('tx_myext_domain_model_user')
    ->where(
        $queryBuilder->expr()->eq('deleted', 0),
        $queryBuilder->expr()->eq('hidden', 0)
    )
    ->executeQuery()
    ->fetchAllAssociative();
```

### Pattern: Query Restrictions

**Before:**
```php
$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
    ->getQueryBuilderForTable('pages');
$queryBuilder->getRestrictions()->removeAll();
$queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
```

**After:**
```php
$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
    ->getQueryBuilderForTable('pages');
$queryBuilder->getRestrictions()
    ->removeAll()
    ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
```

## 3. FlexForm Handling

### Pattern: Modern FlexForm Access

**Before:**
```php
$flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
$flexFormData = $flexFormService->convertFlexFormContentToArray($row['pi_flexform']);
$setting = $flexFormData['settings']['mySetting'] ?? '';
```

**After:**
```php
$flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
$flexFormData = $flexFormService->convertFlexFormContentToArray($row['pi_flexform']);
$setting = $flexFormData['settings']['mySetting'] ?? '';

// Or with direct array access if structure is known
$setting = $row['pi_flexform']['data']['sDEF']['lDEF']['settings.mySetting']['vDEF'] ?? '';
```

## 4. Event Dispatcher (PSR-14)

Replace Signal/Slot with modern Event Dispatcher.

**Before:**
```php
// Registration
$signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
$signalSlotDispatcher->connect(
    MyClass::class,
    'mySignal',
    MySlot::class,
    'mySlot'
);

// Dispatching
$this->objectManager->get(Dispatcher::class)->dispatch(__CLASS__, 'mySignal', [$data]);
```

**After:**
```php
// Event class
final class MyEvent
{
    public function __construct(
        private array $data
    ) {}
    
    public function getData(): array
    {
        return $this->data;
    }
}

// Listener registration in Services.yaml
services:
  MyVendor\MyExt\EventListener\MyEventListener:
    tags:
      - name: event.listener
        identifier: 'my-event-listener'
        event: MyVendor\MyExt\Event\MyEvent

// Dispatching
$event = new MyEvent($data);
$this->eventDispatcher->dispatch($event);
```

## 5. Site Configuration API

### Pattern: Modern Site Handling

**Before:**
```php
$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
$baseUrl = $site->getBase()->getHost();
```

**After:**
```php
$site = $request->getAttribute('site');
$baseUrl = (string)$site->getBase();
```

## 6. Middleware Usage

Replace hooks with PSR-15 middleware.

**Before:**
```php
// ext_localconf.php
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][] = MyClass::class . '->processRequest';
```

**After:**
```php
// Configuration/RequestMiddlewares.php
return [
    'frontend' => [
        'myvendor/myext/my-middleware' => [
            'target' => \MyVendor\MyExt\Middleware\MyMiddleware::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/content-length-headers',
            ],
        ],
    ],
];

// Middleware class
class MyMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Process request
        return $handler->handle($request);
    }
}
```

## 7. Context API

### Pattern: Modern Context Access

**Before:**
```php
$frontendUser = $GLOBALS['TSFE']->fe_user;
$isLoggedIn = $frontendUser->user['uid'] > 0;
```

**After:**
```php
$context = GeneralUtility::makeInstance(Context::class);
$isLoggedIn = $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
```

## 8. Icon API

### Pattern: Modern Icon Rendering

**Before:**
```php
$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
$icon = $iconFactory->getIcon('actions-document-new', Icon::SIZE_SMALL)->render();
```

**After:**
```php
$icon = GeneralUtility::makeInstance(IconFactory::class)
    ->getIcon('actions-document-new', Icon::SIZE_SMALL)
    ->render();

// Or in Fluid
{f:be.buttons.icon(icon: 'actions-document-new')}
```

## 9. TCA Modernization

### Pattern: Modern TCA Configuration

**Before:**
```php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_item',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'title, description'],
    ],
    'columns' => [
        'title' => [
            'exclude' => false,
            'label' => 'LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_item.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
    ],
];
```

**After:**
```php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_item',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                title, description',
        ],
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:my_ext/Resources/Private/Language/locallang_db.xlf:tx_myext_domain_model_item.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
    ],
];
```

## 10. Fluid ViewHelper Simplification

### Pattern: Modern ViewHelper

**Before:**
```php
class MyViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'The value', true);
    }
    
    /**
     * @return string
     */
    public function render()
    {
        return strtoupper($this->arguments['value']);
    }
}
```

**After:**
```php
class MyViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'The value', true);
    }
    
    public function render(): string
    {
        return strtoupper($this->arguments['value']);
    }
}
```

## 11. Dependency Injection Configuration

### Pattern: Services.yaml Configuration

**Before:**
```php
// ext_localconf.php
GeneralUtility::makeInstance(ObjectManager::class)
    ->get(SomeService::class);
```

**After:**
```yaml
# Configuration/Services.yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  MyVendor\MyExt\:
    resource: '../Classes/*'
    
  MyVendor\MyExt\Service\SomeService:
    public: true
```

## When to Apply

Apply TYPO3-specific patterns when:
- Any `typo3/cms-*` package is present in composer.json
- Old Signal/Slot patterns are used
- Hooks can be replaced with middleware
- TCA uses deprecated options
- FlexForm handling can be simplified
- Constructor injection would reduce boilerplate

Do not apply when:
- TYPO3 version < 11 (many features require v11+)
- Extensions need to support older TYPO3 versions
- Breaking changes would affect production stability
