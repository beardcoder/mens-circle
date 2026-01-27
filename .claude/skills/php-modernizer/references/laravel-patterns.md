# Laravel-Specific Optimization Patterns

Framework-specific improvements for Laravel projects. Apply these when `laravel/framework` is detected in composer.json.

## 1. Collections Over Array Functions

Replace array manipulation chains with Laravel Collections for better readability and chainability.

### Pattern: Filter and Map

**Before:**
```php
$activeUserEmails = array_map(
    fn($user) => $user->email,
    array_filter($users, fn($user) => $user->is_active)
);
```

**After:**
```php
$activeUserEmails = collect($users)
    ->filter(fn($user) => $user->is_active)
    ->pluck('email')
    ->all();
```

### Pattern: Group By

**Before:**
```php
$grouped = [];
foreach ($orders as $order) {
    $status = $order->status;
    if (!isset($grouped[$status])) {
        $grouped[$status] = [];
    }
    $grouped[$status][] = $order;
}
```

**After:**
```php
$grouped = collect($orders)->groupBy('status');
```

### Pattern: Sum and Reduce

**Before:**
```php
$total = array_reduce(
    $orders,
    fn($carry, $order) => $carry + $order->total,
    0
);
```

**After:**
```php
$total = collect($orders)->sum('total');
```

### Pattern: Unique Values

**Before:**
```php
$uniqueEmails = array_unique(
    array_map(fn($user) => $user->email, $users)
);
```

**After:**
```php
$uniqueEmails = collect($users)->pluck('email')->unique();
```

## 2. Eloquent Query Optimization

Replace manual SQL, joins, and suboptimal queries with Eloquent features.

### Pattern: Relationship Queries

**Before:**
```php
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->where('posts.published', true)
    ->select('users.*')
    ->distinct()
    ->get();
```

**After:**
```php
$users = User::whereHas('posts', function ($query) {
    $query->where('published', true);
})->get();
```

### Pattern: Eager Loading

**Before:**
```php
$users = User::all();
foreach ($users as $user) {
    // N+1 query problem
    echo $user->profile->bio;
}
```

**After:**
```php
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->bio;
}
```

### Pattern: Count Queries

**Before:**
```php
$users = User::where('active', true)->get();
$count = count($users);
```

**After:**
```php
$count = User::where('active', true)->count();
```

### Pattern: Existence Checks

**Before:**
```php
$hasActiveUsers = count(User::where('active', true)->get()) > 0;
```

**After:**
```php
$hasActiveUsers = User::where('active', true)->exists();
```

## 3. Route Model Binding

Replace manual model lookups with route model binding.

### Pattern: Basic Binding

**Before:**
```php
Route::get('/users/{id}', function ($id) {
    $user = User::findOrFail($id);
    return view('user.show', compact('user'));
});
```

**After:**
```php
Route::get('/users/{user}', function (User $user) {
    return view('user.show', compact('user'));
});
```

### Pattern: Custom Key Binding

**Before:**
```php
Route::get('/users/{slug}', function ($slug) {
    $user = User::where('slug', $slug)->firstOrFail();
    return view('user.show', compact('user'));
});
```

**After:**
```php
// In User model
public function getRouteKeyName()
{
    return 'slug';
}

// In routes
Route::get('/users/{user:slug}', function (User $user) {
    return view('user.show', compact('user'));
});
```

## 4. Request Validation

Use Form Request classes instead of inline validation.

**Before:**
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'body' => 'required',
        'status' => 'required|in:draft,published',
    ]);
    
    Post::create($validated);
    
    return redirect()->route('posts.index');
}
```

**After:**
```php
// app/Http/Requests/StorePostRequest.php
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'body' => 'required',
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}

// Controller
public function store(StorePostRequest $request)
{
    Post::create($request->validated());
    
    return redirect()->route('posts.index');
}
```

## 5. Response Helpers

Use Laravel's response helpers instead of manual JSON responses.

**Before:**
```php
return response()->json([
    'success' => true,
    'data' => $user,
    'message' => 'User created successfully'
], 201);
```

**After:**
```php
return response()->json([
    'data' => $user,
    'message' => 'User created successfully'
], 201);

// Or even better with API Resources
return new UserResource($user);
```

### Pattern: Error Responses

**Before:**
```php
return response()->json([
    'success' => false,
    'message' => 'Validation failed',
    'errors' => $validator->errors()
], 422);
```

**After:**
```php
return response()->json([
    'message' => 'Validation failed',
    'errors' => $validator->errors()
], 422);

// Or throw ValidationException to let Laravel handle it
throw ValidationException::withMessages($errors);
```

## 6. Conditional Queries

Use Eloquent's conditional query methods.

**Before:**
```php
$query = User::query();

if ($request->has('active')) {
    $query->where('active', $request->active);
}

if ($request->has('role')) {
    $query->where('role', $request->role);
}

$users = $query->get();
```

**After:**
```php
$users = User::query()
    ->when($request->has('active'), fn($q) => $q->where('active', $request->active))
    ->when($request->has('role'), fn($q) => $q->where('role', $request->role))
    ->get();
```

## 7. Higher Order Messages

Use collection higher order messages for cleaner iteration.

**Before:**
```php
$users->each(function ($user) {
    $user->notify(new WelcomeNotification);
});
```

**After:**
```php
$users->each->notify(new WelcomeNotification);
```

**Before:**
```php
$activeUsers = $users->filter(function ($user) {
    return $user->isActive();
});
```

**After:**
```php
$activeUsers = $users->filter->isActive();
```

## 8. Attribute Casting

Use modern attribute casting in models.

**Before:**
```php
class Post extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'is_featured' => 'boolean',
    ];
    
    public function getTagsAttribute($value)
    {
        return json_decode($value, true);
    }
    
    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = json_encode($value);
    }
}
```

**After:**
```php
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Post extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'is_featured' => 'boolean',
        'tags' => AsArrayObject::class,
    ];
}
```

## 9. Value Objects and Enums

Replace string status fields with Enums.

**Before:**
```php
class Order extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
```

**After:**
```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    
    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Processing => 'blue',
            self::Completed => 'green',
        };
    }
}

class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
```

## 10. Pipeline Pattern

Replace nested service calls with Laravel's Pipeline.

**Before:**
```php
$data = $this->validateInput($request->all());
$data = $this->sanitizeData($data);
$data = $this->enrichData($data);
$data = $this->transformData($data);
return $this->process($data);
```

**After:**
```php
return Pipeline::send($request->all())
    ->through([
        ValidateInput::class,
        SanitizeData::class,
        EnrichData::class,
        TransformData::class,
    ])
    ->then(fn($data) => $this->process($data));
```

## 11. Conditional Blade Directives

Use Laravel's built-in directives instead of PHP conditionals in Blade.

**Before:**
```blade
<?php if (auth()->check()): ?>
    <p>Welcome, <?= auth()->user()->name ?></p>
<?php endif; ?>
```

**After:**
```blade
@auth
    <p>Welcome, {{ auth()->user()->name }}</p>
@endauth
```

## 12. Accessor and Mutator Attributes (Laravel 9+)

Use the new Attribute-based accessors instead of get/set methods.

**Before:**
```php
public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}

public function setPasswordAttribute($value)
{
    $this->attributes['password'] = bcrypt($value);
}
```

**After:**
```php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn() => "{$this->first_name} {$this->last_name}",
    );
}

protected function password(): Attribute
{
    return Attribute::make(
        set: fn($value) => bcrypt($value),
    );
}
```

## When to Apply

Apply Laravel-specific patterns when:
- `laravel/framework` is present in composer.json
- Code uses array functions where Collections would be clearer
- Manual queries can be replaced with Eloquent features
- Framework features would reduce boilerplate significantly

Do not apply when:
- Performance is critical and raw queries are necessary
- The application is in maintenance mode with no Laravel updates planned
- Team prefers vanilla PHP patterns
