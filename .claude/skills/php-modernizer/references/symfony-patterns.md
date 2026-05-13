# Symfony-Specific Optimization Patterns

Framework-specific improvements for Symfony projects. Apply these when `symfony/*` packages are detected in composer.json.

## 1. Autowiring and Dependency Injection

### Pattern: Modern Service Configuration

**Before:**

```yaml
# services.yaml
services:
    App\Service\UserService:
        arguments:
            - "@doctrine.orm.entity_manager"
            - "@security.password_encoder"
            - "@event_dispatcher"
```

**After:**

```yaml
# services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: "../src/"
        exclude:
            - "../src/DependencyInjection/"
            - "../src/Entity/"
            - "../src/Kernel.php"
```

**PHP Class:**

```php
class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}
}
```

## 2. Controller Optimization

### Pattern: AbstractController Features

**Before:**

```php
class UserController extends Controller
{
    public function show(int $id): Response
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
```

**After:**

```php
class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user_show')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
```

### Pattern: Attributes over Annotations

**Before:**

```php
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/users", name="api_users_")
 */
class UserApiController extends AbstractController
{
    /**
     * @Route("/", name="list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        // ...
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(User $user): JsonResponse
    {
        // ...
    }
}
```

**After:**

```php
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_users_')]
class UserApiController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // ...
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        // ...
    }
}
```

## 3. Form Handling

### Pattern: Modern Form Building

**Before:**

```php
public function new(Request $request): Response
{
    $user = new User();

    $form = $this->createFormBuilder($user)
        ->add('email', EmailType::class)
        ->add('password', PasswordType::class)
        ->add('save', SubmitType::class)
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }

    return $this->render('user/new.html.twig', [
        'form' => $form->createView(),
    ]);
}
```

**After:**

```php
#[Route('/user/new', name: 'user_new')]
public function new(
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }

    return $this->render('user/new.html.twig', [
        'form' => $form,
    ]);
}
```

## 4. Doctrine Query Optimization

### Pattern: Query Builder vs DQL

**Before:**

```php
$query = $this->entityManager->createQuery(
    'SELECT u FROM App\Entity\User u
     WHERE u.active = :active
     AND u.role = :role
     ORDER BY u.createdAt DESC'
);
$query->setParameter('active', true);
$query->setParameter('role', 'admin');
$users = $query->getResult();
```

**After:**

```php
$users = $this->entityManager->getRepository(User::class)
    ->createQueryBuilder('u')
    ->where('u.active = :active')
    ->andWhere('u.role = :role')
    ->setParameter('active', true)
    ->setParameter('role', 'admin')
    ->orderBy('u.createdAt', 'DESC')
    ->getQuery()
    ->getResult();
```

### Pattern: Custom Repository Methods

**Before:**

```php
class UserController extends AbstractController
{
    public function list(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.active = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('user/list.html.twig', ['users' => $users]);
    }
}
```

**After:**

```php
// Repository
class UserRepository extends ServiceEntityRepository
{
    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.active = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

// Controller
class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list')]
    public function list(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findActiveUsers(),
        ]);
    }
}
```

## 5. Event Dispatcher

### Pattern: Event Subscribers

**Before:**

```php
// Services.yaml
services:
    App\EventListener\UserListener:
        tags:
            - { name: kernel.event_listener, event: user.created, method: onUserCreated }
```

**After:**

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::class => 'onUserCreated',
            UserUpdatedEvent::class => 'onUserUpdated',
        ];
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        // Handle event
    }

    public function onUserUpdated(UserUpdatedEvent $event): void
    {
        // Handle event
    }
}
```

## 6. Validation

### Pattern: Validation Attributes

**Before:**

```php
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    /**
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=8)
     */
    private $password;
}
```

**After:**

```php
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    private string $password;
}
```

## 7. Security

### Pattern: Modern Security Voters

**Before:**

```php
class PostVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // ...
    }
}
```

**After:**

```php
enum PostPermission: string
{
    case View = 'view';
    case Edit = 'edit';
}

class PostVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Post
            && PostPermission::tryFrom($attribute) !== null;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $permission = PostPermission::from($attribute);

        return match ($permission) {
            PostPermission::View => $this->canView($subject, $token),
            PostPermission::Edit => $this->canEdit($subject, $token),
        };
    }
}
```

## 8. Serializer

### Pattern: Serialization Groups with Attributes

**Before:**

```php
use Symfony\Component\Serializer\Annotation\Groups;

class User
{
    /**
     * @Groups({"user:read", "user:write"})
     */
    private $email;

    /**
     * @Groups({"user:read"})
     */
    private $createdAt;
}
```

**After:**

```php
use Symfony\Component\Serializer\Attribute\Groups;

class User
{
    #[Groups(['user:read', 'user:write'])]
    private string $email;

    #[Groups(['user:read'])]
    private \DateTimeInterface $createdAt;
}
```

## 9. HTTP Client

### Pattern: Modern HTTP Client Usage

**Before:**

```php
$client = HttpClient::create();
$response = $client->request('GET', 'https://api.example.com/users');
$statusCode = $response->getStatusCode();
$content = $response->getContent();
$data = json_decode($content, true);
```

**After:**

```php
$response = $this->httpClient->request('GET', 'https://api.example.com/users');
$data = $response->toArray(); // Automatically decodes JSON
```

## 10. Console Commands

### Pattern: Modern Command Definition

**Before:**

```php
class UserCreateCommand extends Command
{
    protected static $defaultName = 'app:user:create';

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new user')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Make user admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $isAdmin = $input->getOption('admin');

        // ...

        return Command::SUCCESS;
    }
}
```

**After:**

```php
#[AsCommand(
    name: 'app:user:create',
    description: 'Creates a new user'
)]
class UserCreateCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Make user admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $isAdmin = $input->getOption('admin');

        // ...

        $io->success('User created successfully!');

        return Command::SUCCESS;
    }
}
```

## 11. Entity Listeners

### Pattern: Entity Lifecycle Callbacks

**Before:**

```php
/**
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
```

**After:**

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class User
{
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
```

## 12. Messenger Component

### Pattern: Asynchronous Message Handling

**Before:**

```php
public function sendEmail(UserRepository $users, MailerInterface $mailer): void
{
    $users = $users->findAll();
    foreach ($users as $user) {
        $email = (new Email())
            ->to($user->getEmail())
            ->subject('Newsletter')
            ->html('...');
        $mailer->send($email);
    }
}
```

**After:**

```php
// Message
readonly class SendNewsletterMessage
{
    public function __construct(
        public int $userId,
    ) {}
}

// Handler
#[AsMessageHandler]
class SendNewsletterHandler
{
    public function __construct(
        private UserRepository $users,
        private MailerInterface $mailer,
    ) {}

    public function __invoke(SendNewsletterMessage $message): void
    {
        $user = $this->users->find($message->userId);

        $email = (new Email())
            ->to($user->getEmail())
            ->subject('Newsletter')
            ->html('...');

        $this->mailer->send($email);
    }
}

// Controller
public function sendNewsletter(
    UserRepository $users,
    MessageBusInterface $bus
): Response {
    foreach ($users->findAll() as $user) {
        $bus->dispatch(new SendNewsletterMessage($user->getId()));
    }

    return $this->redirectToRoute('admin_dashboard');
}
```

## When to Apply

Apply Symfony-specific patterns when:

- `symfony/*` packages are present in composer.json
- Using annotations that can be replaced with attributes (PHP 8.0+)
- Autowiring can simplify service configuration
- Custom repositories would encapsulate query logic
- Message queues would improve performance
- Event subscribers are more appropriate than listeners

Do not apply when:

- Symfony version < 5.4 (many features require 5.4+)
- PHP version < 8.0 (attributes require PHP 8.0+)
- Breaking changes would affect production stability
- Team prefers older patterns for consistency
