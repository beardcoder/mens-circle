# PHP 8.4+ Features and Patterns

Comprehensive guide to modern PHP syntax and features for code modernization.

## 1. Match Expressions (PHP 8.0+)

Replace complex if-else chains and switch statements with concise match expressions.

### Pattern: Simple Conditionals

**Before:**

```php
if (!$event->is_published) {
    return ['error' => 'Not available', 'code' => 404];
} elseif ($event->isPast) {
    return ['error' => 'Event passed', 'code' => 410];
} elseif ($event->isFull) {
    return ['error' => 'Event full', 'code' => 409];
} else {
    return null;
}
```

**After:**

```php
return match (true) {
    !$event->is_published => ['error' => 'Not available', 'code' => 404],
    $event->isPast        => ['error' => 'Event passed', 'code' => 410],
    $event->isFull        => ['error' => 'Event full', 'code' => 409],
    default               => null,
};
```

### Pattern: Status Mapping

**Before:**

```php
switch ($status) {
    case 'pending':
        return 'warning';
    case 'approved':
        return 'success';
    case 'rejected':
        return 'danger';
    default:
        return 'secondary';
}
```

**After:**

```php
return match ($status) {
    'pending'  => 'warning',
    'approved' => 'success',
    'rejected' => 'danger',
    default    => 'secondary',
};
```

## 2. Constructor Property Promotion (PHP 8.0+)

Eliminate boilerplate constructor code by promoting parameters directly to properties.

**Before:**

```php
class UserService
{
    private UserRepository $repository;
    private Validator $validator;
    private EventDispatcher $dispatcher;

    public function __construct(
        UserRepository $repository,
        Validator $validator,
        EventDispatcher $dispatcher
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->dispatcher = $dispatcher;
    }
}
```

**After:**

```php
class UserService
{
    public function __construct(
        private UserRepository $repository,
        private Validator $validator,
        private EventDispatcher $dispatcher,
    ) {}
}
```

## 3. Readonly Properties (PHP 8.1+)

Add immutability guarantees to properties that shouldn't change after initialization.

**Before:**

```php
class Event
{
    private string $id;
    private DateTimeImmutable $createdAt;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->createdAt = new DateTimeImmutable();
    }
}
```

**After:**

```php
class Event
{
    public function __construct(
        private readonly string $id,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}
}
```

## 4. Enums (PHP 8.1+)

Replace string/int constants with type-safe enums.

**Before:**

```php
class Order
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';

    private string $status;

    public function setStatus(string $status): void
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
        ])) {
            throw new InvalidArgumentException('Invalid status');
        }
        $this->status = $status;
    }
}
```

**After:**

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
}

class Order
{
    private OrderStatus $status;

    public function setStatus(OrderStatus $status): void
    {
        $this->status = $status;
    }
}
```

### Enum with Methods

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function canBeCancelled(): bool
    {
        return match ($this) {
            self::Pending, self::Processing => true,
            self::Shipped, self::Delivered => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting Processing',
            self::Processing => 'In Progress',
            self::Shipped => 'On the Way',
            self::Delivered => 'Completed',
        };
    }
}
```

## 5. Nullsafe Operator (PHP 8.0+)

Simplify null checks with the nullsafe operator `?->`.

**Before:**

```php
$country = null;
if ($user !== null) {
    $address = $user->getAddress();
    if ($address !== null) {
        $country = $address->getCountry();
    }
}
```

**After:**

```php
$country = $user?->getAddress()?->getCountry();
```

**Before:**

```php
$length = $data !== null && $data->getName() !== null
    ? strlen($data->getName())
    : 0;
```

**After:**

```php
$length = strlen($data?->getName() ?? '') ?: 0;
```

## 6. Named Arguments (PHP 8.0+)

Improve readability for functions with many parameters or optional parameters.

**Before:**

```php
// What do these booleans mean?
$user = createUser('john@example.com', 'password123', true, false, true);

// Skipping optional params is verbose
$user = createUser('john@example.com', 'password123', null, null, true);
```

**After:**

```php
$user = createUser(
    email: 'john@example.com',
    password: 'password123',
    verified: true,
    sendWelcomeEmail: true,
);
```

## 7. Array Unpacking / Spread Operator (PHP 7.4+)

Modern array manipulation with spread operator.

**Before:**

```php
$defaults = ['color' => 'blue', 'size' => 'medium'];
$overrides = ['size' => 'large'];
$config = array_merge($defaults, $overrides);
```

**After:**

```php
$config = [...$defaults, ...$overrides];
```

**Before:**

```php
$filtered = array_filter($items, fn($item) => $item > 10);
$mapped = array_map(fn($item) => $item * 2, $filtered);
```

**After:**

```php
$result = [...array_map(
    fn($item) => $item * 2,
    array_filter($items, fn($item) => $item > 10)
)];
```

## 8. String Interpolation

Replace sprintf with modern string interpolation for better readability.

**Before:**

```php
$message = sprintf(
    'Welcome, %s! You have %d new messages.',
    $user->getName(),
    $messageCount
);
```

**After:**

```php
$message = "Welcome, {$user->getName()}! You have {$messageCount} new messages.";
```

**Before:**

```php
$url = sprintf('https://api.example.com/%s/%s', $version, $endpoint);
```

**After:**

```php
$url = "https://api.example.com/{$version}/{$endpoint}";
```

## 9. First-Class Callables (PHP 8.1+)

Use concise syntax for passing methods as callbacks.

**Before:**

```php
$names = array_map(fn($user) => $user->getName(), $users);
$sorted = array_filter($items, fn($item) => $this->isValid($item));
```

**After:**

```php
$names = array_map($user->getName(...), $users);
$sorted = array_filter($items, $this->isValid(...));
```

## 10. Array Destructuring

Modern array handling with destructuring.

**Before:**

```php
$parts = explode(':', $connection);
$host = $parts[0];
$port = isset($parts[1]) ? $parts[1] : 3306;
```

**After:**

```php
[$host, $port] = explode(':', $connection) + [1 => 3306];
```

**Before:**

```php
$config = $this->loadConfig();
$database = $config['database'];
$cache = $config['cache'];
```

**After:**

```php
['database' => $database, 'cache' => $cache] = $this->loadConfig();
```

## 11. Typed Properties (PHP 7.4+)

Add type declarations to all properties for better type safety.

**Before:**

```php
class User
{
    private $id;
    private $email;
    private $roles;
    private $createdAt;
}
```

**After:**

```php
class User
{
    private int $id;
    private string $email;
    private array $roles;
    private DateTimeImmutable $createdAt;
}
```

## 12. Union Types (PHP 8.0+)

Express multiple possible types without docblocks.

**Before:**

```php
/**
 * @param int|string $id
 * @return User|null
 */
public function findUser($id)
{
    // ...
}
```

**After:**

```php
public function findUser(int|string $id): ?User
{
    // ...
}
```

## 13. Throw Expressions (PHP 8.0+)

Use throw as an expression in various contexts.

**Before:**

```php
$value = isset($data['key']) ? $data['key'] : throw new Exception('Missing key');
// Syntax error in PHP < 8.0
```

**After:**

```php
$value = $data['key'] ?? throw new Exception('Missing key');
```

## Combination Patterns

### Pattern: Modern DTO

**Before:**

```php
class UserDTO
{
    public $id;
    public $email;
    public $status;

    public function __construct($id, $email, $status)
    {
        $this->id = $id;
        $this->email = $email;
        $this->status = $status;
    }
}
```

**After:**

```php
enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}

readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public UserStatus $status,
    ) {}
}
```

### Pattern: Modern Service Class

**Before:**

```php
class NotificationService
{
    private $mailer;
    private $smsProvider;
    private $logger;

    public function __construct($mailer, $smsProvider, $logger)
    {
        $this->mailer = $mailer;
        $this->smsProvider = $smsProvider;
        $this->logger = $logger;
    }

    public function send($user, $message, $channel)
    {
        if ($channel === 'email') {
            $this->mailer->send($user->getEmail(), $message);
            $this->logger->info('Email sent');
        } elseif ($channel === 'sms') {
            $this->smsProvider->send($user->getPhone(), $message);
            $this->logger->info('SMS sent');
        } else {
            throw new \InvalidArgumentException('Invalid channel');
        }
    }
}
```

**After:**

```php
enum NotificationChannel
{
    case Email;
    case Sms;
}

class NotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly SmsProviderInterface $smsProvider,
        private readonly LoggerInterface $logger,
    ) {}

    public function send(User $user, string $message, NotificationChannel $channel): void
    {
        match ($channel) {
            NotificationChannel::Email => $this->sendEmail($user, $message),
            NotificationChannel::Sms => $this->sendSms($user, $message),
        };
    }

    private function sendEmail(User $user, string $message): void
    {
        $this->mailer->send(to: $user->email, body: $message);
        $this->logger->info('Email sent', ['user' => $user->id]);
    }

    private function sendSms(User $user, string $message): void
    {
        $this->smsProvider->send(to: $user->phone, body: $message);
        $this->logger->info('SMS sent', ['user' => $user->id]);
    }
}
```

## When to Apply

Apply these patterns when:

- The old syntax obscures intent
- Type safety would prevent bugs
- Readability improves significantly
- Boilerplate code can be eliminated
- Modern features better express the domain

Do not apply when:

- The change would reduce clarity
- Team hasn't adopted PHP 8.0+ conventions
- Breaking changes would impact too many dependents
