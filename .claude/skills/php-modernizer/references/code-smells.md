# Code Smells and Simplification Patterns

General code quality improvements independent of PHP version or framework. These patterns address overengineering, unnecessary complexity, and poor readability.

## 1. Complexity Reduction

### Pattern: Flatten Nested Conditionals

**Before:**
```php
public function processOrder(Order $order): bool
{
    if ($order->isValid()) {
        if ($order->hasStock()) {
            if ($order->user->hasPaymentMethod()) {
                if ($this->paymentGateway->charge($order)) {
                    $this->fulfillment->process($order);
                    return true;
                } else {
                    throw new PaymentException('Payment failed');
                }
            } else {
                throw new ValidationException('No payment method');
            }
        } else {
            throw new ValidationException('Out of stock');
        }
    } else {
        throw new ValidationException('Invalid order');
    }
}
```

**After:**
```php
public function processOrder(Order $order): bool
{
    if (!$order->isValid()) {
        throw new ValidationException('Invalid order');
    }
    
    if (!$order->hasStock()) {
        throw new ValidationException('Out of stock');
    }
    
    if (!$order->user->hasPaymentMethod()) {
        throw new ValidationException('No payment method');
    }
    
    if (!$this->paymentGateway->charge($order)) {
        throw new PaymentException('Payment failed');
    }
    
    $this->fulfillment->process($order);
    
    return true;
}
```

### Pattern: Extract Complex Boolean Logic

**Before:**
```php
if (($user->age >= 18 && $user->hasLicense && !$user->isBanned) || 
    ($user->age >= 16 && $user->hasParentalConsent && $user->hasLicense)) {
    $this->allowAccess($user);
}
```

**After:**
```php
$canAccessAsAdult = $user->age >= 18 
    && $user->hasLicense 
    && !$user->isBanned;
    
$canAccessAsMinor = $user->age >= 16 
    && $user->hasParentalConsent 
    && $user->hasLicense;

if ($canAccessAsAdult || $canAccessAsMinor) {
    $this->allowAccess($user);
}
```

### Pattern: Replace Complex Ternary

**Before:**
```php
$discount = $user->isPremium() 
    ? ($order->total > 100 
        ? ($order->hasPromoCode ? 0.30 : 0.20) 
        : 0.10)
    : 0;
```

**After:**
```php
$discount = match (true) {
    !$user->isPremium() => 0,
    $order->total <= 100 => 0.10,
    $order->hasPromoCode => 0.30,
    default => 0.20,
};
```

## 2. Remove Unnecessary Abstractions

### Pattern: Over-Abstracted Interfaces

**Before:**
```php
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function save(User $user): void;
}

interface UserServiceInterface
{
    public function getUser(int $id): User;
    public function updateUser(User $user): void;
}

class UserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?User { /* ... */ }
    public function save(User $user): void { /* ... */ }
}

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}
    
    public function getUser(int $id): User
    {
        return $this->repository->find($id) 
            ?? throw new NotFoundException();
    }
    
    public function updateUser(User $user): void
    {
        $this->repository->save($user);
    }
}
```

**After:**
```php
// If there's only one implementation, remove the interface
class UserRepository
{
    public function find(int $id): ?User { /* ... */ }
    public function save(User $user): void { /* ... */ }
}

// If service only wraps repository, remove the service layer
// Use repository directly in controllers
```

### Pattern: Unnecessary Wrapper Methods

**Before:**
```php
class OrderService
{
    public function __construct(
        private OrderRepository $repository
    ) {}
    
    public function findOrder(int $id): ?Order
    {
        return $this->repository->find($id);
    }
    
    public function saveOrder(Order $order): void
    {
        $this->repository->save($order);
    }
    
    public function deleteOrder(Order $order): void
    {
        $this->repository->delete($order);
    }
}
```

**After:**
```php
// Remove service, use repository directly
// Add methods only when they add actual business logic
class OrderService
{
    public function __construct(
        private OrderRepository $repository,
        private EventDispatcher $dispatcher
    ) {}
    
    public function placeOrder(Order $order): void
    {
        // Actual business logic
        $order->calculateTotal();
        $this->repository->save($order);
        $this->dispatcher->dispatch(new OrderPlacedEvent($order));
    }
}
```

## 3. Eliminate Redundancy

### Pattern: Remove Obvious Comments

**Before:**
```php
// Get the user from database
$user = $this->repository->find($id);

// Check if user exists
if ($user === null) {
    // Throw exception if not found
    throw new NotFoundException('User not found');
}

// Update user email
$user->setEmail($email);

// Save user to database
$this->repository->save($user);
```

**After:**
```php
$user = $this->repository->find($id) 
    ?? throw new NotFoundException('User not found');

$user->setEmail($email);
$this->repository->save($user);
```

### Pattern: Consolidate Duplicate Logic

**Before:**
```php
public function sendWelcomeEmail(User $user): void
{
    $email = (new Email())
        ->to($user->email)
        ->subject('Welcome!')
        ->html($this->renderTemplate('emails/welcome.html.twig', ['user' => $user]));
    
    $this->mailer->send($email);
}

public function sendResetPasswordEmail(User $user, string $token): void
{
    $email = (new Email())
        ->to($user->email)
        ->subject('Reset Password')
        ->html($this->renderTemplate('emails/reset.html.twig', [
            'user' => $user,
            'token' => $token
        ]));
    
    $this->mailer->send($email);
}
```

**After:**
```php
private function sendEmail(User $user, string $template, array $data = []): void
{
    $email = (new Email())
        ->to($user->email)
        ->subject($data['subject'] ?? 'Notification')
        ->html($this->renderTemplate($template, ['user' => $user, ...$data]));
    
    $this->mailer->send($email);
}

public function sendWelcomeEmail(User $user): void
{
    $this->sendEmail($user, 'emails/welcome.html.twig', [
        'subject' => 'Welcome!'
    ]);
}

public function sendResetPasswordEmail(User $user, string $token): void
{
    $this->sendEmail($user, 'emails/reset.html.twig', [
        'subject' => 'Reset Password',
        'token' => $token
    ]);
}
```

## 4. God Methods / Classes

### Pattern: Break Down Large Methods

**Before:**
```php
public function processCheckout(array $data): Order
{
    // Validation (20 lines)
    if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException('Invalid email');
    }
    // ... more validation
    
    // Create order (30 lines)
    $order = new Order();
    $order->setUser($user);
    // ... more setup
    
    // Process payment (25 lines)
    $paymentResult = $this->paymentGateway->charge(...);
    // ... handle payment
    
    // Send emails (15 lines)
    $this->mailer->send(...);
    // ... more emails
    
    return $order;
}
```

**After:**
```php
public function processCheckout(array $data): Order
{
    $validatedData = $this->validateCheckoutData($data);
    $order = $this->createOrder($validatedData);
    $this->processPayment($order);
    $this->sendOrderConfirmation($order);
    
    return $order;
}

private function validateCheckoutData(array $data): array
{
    // Validation logic
}

private function createOrder(array $data): Order
{
    // Order creation logic
}

private function processPayment(Order $order): void
{
    // Payment logic
}

private function sendOrderConfirmation(Order $order): void
{
    // Email logic
}
```

## 5. Descriptive Naming

### Pattern: Replace Magic Numbers

**Before:**
```php
if ($order->status === 3) {
    // Process shipped orders
}

if ($user->level >= 5) {
    // Premium features
}
```

**After:**
```php
enum OrderStatus: int
{
    case Pending = 1;
    case Processing = 2;
    case Shipped = 3;
    case Delivered = 4;
}

if ($order->status === OrderStatus::Shipped) {
    // Process shipped orders
}

const PREMIUM_LEVEL = 5;
if ($user->level >= self::PREMIUM_LEVEL) {
    // Premium features
}
```

### Pattern: Improve Variable Names

**Before:**
```php
$d = new DateTime();
$u = $this->repo->find($id);
$temp = $u->getOrders();
$cnt = count($temp);
```

**After:**
```php
$currentDate = new DateTime();
$user = $this->repository->find($id);
$orders = $user->getOrders();
$orderCount = count($orders);
```

## 6. Performance Anti-Patterns

### Pattern: Query in Loops (N+1 Problem)

**Before:**
```php
$users = $this->userRepository->findAll();
foreach ($users as $user) {
    $orderCount = count($user->getOrders()); // N+1 query!
    echo "{$user->name}: {$orderCount} orders";
}
```

**After:**
```php
// Eager load relationships
$users = $this->userRepository->findAllWithOrders();
foreach ($users as $user) {
    $orderCount = count($user->getOrders()); // No query
    echo "{$user->name}: {$orderCount} orders";
}

// Or use a COUNT query
$usersWithCounts = $this->userRepository->findAllWithOrderCounts();
```

### Pattern: Unnecessary Loops

**Before:**
```php
$sum = 0;
foreach ($orders as $order) {
    $sum += $order->total;
}

$emails = [];
foreach ($users as $user) {
    $emails[] = $user->email;
}
```

**After:**
```php
$sum = array_sum(array_column($orders, 'total'));
// Or with collections: $orders->sum('total')

$emails = array_column($users, 'email');
// Or with collections: $users->pluck('email')
```

## 7. Error Handling

### Pattern: Specific Exceptions

**Before:**
```php
public function charge(Order $order): void
{
    try {
        $this->gateway->process($order);
    } catch (\Exception $e) {
        throw new \Exception('Payment failed: ' . $e->getMessage());
    }
}
```

**After:**
```php
public function charge(Order $order): void
{
    try {
        $this->gateway->process($order);
    } catch (GatewayTimeoutException $e) {
        throw new PaymentTimeoutException(
            "Payment gateway timed out for order {$order->id}",
            previous: $e
        );
    } catch (InsufficientFundsException $e) {
        throw new PaymentDeclinedException(
            "Insufficient funds for order {$order->id}",
            previous: $e
        );
    }
}
```

## 8. Type Safety

### Pattern: Add Missing Type Hints

**Before:**
```php
class UserService
{
    private $repository;
    private $validator;
    
    public function __construct($repository, $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }
    
    public function createUser($data)
    {
        if ($this->validator->validate($data)) {
            return $this->repository->save($data);
        }
        return null;
    }
}
```

**After:**
```php
class UserService
{
    public function __construct(
        private UserRepository $repository,
        private ValidatorInterface $validator,
    ) {}
    
    public function createUser(array $data): User
    {
        if (!$this->validator->validate($data)) {
            throw new ValidationException('Invalid user data');
        }
        
        return $this->repository->save($data);
    }
}
```

## 9. Simplify Conditionals

### Pattern: Positive Logic

**Before:**
```php
if (!$user->isNotActive()) {
    // Do something
}

if (!empty($errors)) {
    // Handle errors
}
```

**After:**
```php
if ($user->isActive()) {
    // Do something
}

if ($errors) {
    // Handle errors
}
```

### Pattern: Remove Double Negatives

**Before:**
```php
if (!$user->isNotVerified()) {
    $this->grantAccess();
}
```

**After:**
```php
if ($user->isVerified()) {
    $this->grantAccess();
}
```

## 10. Immutability Patterns

### Pattern: Use Readonly and Final

**Before:**
```php
class Configuration
{
    private string $apiKey;
    private string $apiSecret;
    
    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }
    
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey; // Dangerous mutation
    }
}
```

**After:**
```php
final readonly class Configuration
{
    public function __construct(
        public string $apiKey,
        public string $apiSecret,
    ) {}
    
    // No setters - object is immutable
}
```

## When to Apply

Apply these simplifications when:
- Code is harder to understand than necessary
- Abstractions add no value
- Logic can be flattened without losing clarity
- Type safety would prevent bugs
- Names don't clearly express intent
- Performance could be improved without complexity

Do not apply when:
- Current code is already clear and maintainable
- Abstraction is necessary for testing or flexibility
- Changes would break existing patterns team relies on
- Performance optimization would reduce readability
