# Development Workflow Guide

## Quick Start

### Prerequisites
- PHP 8.5+
- Composer 2.x
- Bun 1.3.9+
- DDEV (for local development)

### Initial Setup

1. **Install PHP Dependencies**
   ```bash
   composer install --ignore-platform-req=ext-redis
   ```

2. **Install Frontend Dependencies**
   ```bash
   bun install
   ```

3. **Build Frontend Assets**
   ```bash
   bun run build
   ```

### Development Commands

#### PHP Quality Tools

```bash
# Run all quality checks
composer qa:check

# Fix code style issues automatically
composer cs:fix

# Run PHPStan static analysis
composer phpstan

# Generate PHPStan baseline (for gradual adoption)
composer phpstan:baseline
```

#### Frontend Development

```bash
# Development mode with hot reload
bun run dev

# Production build
bun run build

# Watch mode for development
bun run build:watch

# Preview production build
bun run preview
```

#### TYPO3 Commands

```bash
# Setup TYPO3
composer setup

# Clear caches
vendor/bin/typo3 cache:flush

# Import Laravel SQL dump
ddev exec vendor/bin/typo3 menscircle:import:laravel-sql packages/mens_circle/live.sql --truncate -n

# Run messenger worker
vendor/bin/typo3 messenger:consume doctrine

# Dispatch event reminders
vendor/bin/typo3 menscircle:events:dispatch-reminders --hours-before=24
```

## Code Quality Standards

### PHP Standards

- **PSR-12** compliant code style
- **Strict typing** (`declare(strict_types=1);`) in all files
- **PHPStan Level 5** with zero errors
- **PHP 8.5** features encouraged (readonly, enums, promoted properties)
- **Return types** on all methods
- **Property types** on all properties

### TypeScript Standards

- **Strict mode** enabled
- **No implicit any**
- **Proper typing** for all functions and variables
- **ESLint** compliance (when configured)

### File Organization

```
packages/mens_circle/
├── Classes/
│   ├── Command/          # CLI commands
│   ├── Controller/       # Frontend & Backend controllers
│   ├── Domain/
│   │   ├── Enum/        # PHP 8.1+ enums
│   │   ├── Model/       # Domain models
│   │   └── Repository/  # Repository classes
│   ├── Message/         # Symfony Messenger messages
│   ├── MessageHandler/  # Message handlers
│   └── Service/         # Business logic
├── Configuration/
│   ├── Backend/         # Backend modules
│   ├── FlexForms/       # FlexForm definitions
│   ├── Sets/            # Site Set configuration
│   └── TCA/             # Table configuration
└── Resources/
    ├── Private/
    │   ├── Frontend/
    │   │   ├── Scripts/ # TypeScript sources
    │   │   └── Styles/  # CSS sources
    │   ├── Language/    # XLIFF translations
    │   └── Templates/   # Fluid templates
    └── Public/          # Compiled assets
```

## Git Workflow

### Before Committing

1. Run quality checks:
   ```bash
   composer qa
   ```

2. Build frontend:
   ```bash
   bun run build
   ```

3. Test manually:
   - Forms submission
   - Navigation
   - Backend modules

### Commit Message Format

```
<type>: <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `refactor`: Code refactoring
- `style`: Code style changes (formatting)
- `docs`: Documentation changes
- `test`: Adding or updating tests
- `chore`: Build process or tooling changes

**Example:**
```
feat: integrate Hotwired Turbo for SPA navigation

- Add @hotwired/turbo dependency
- Update forms to use Turbo events
- Configure Turbo Drive for automatic navigation
- Update App.entry.ts with Turbo lifecycle hooks

Closes #123
```

## Testing Checklist

### Before Each Release

- [ ] All PHPStan errors resolved
- [ ] All PHP CS Fixer issues resolved
- [ ] Frontend builds without errors
- [ ] Manual testing completed:
  - [ ] Event registration form
  - [ ] Newsletter signup
  - [ ] Testimonial submission
  - [ ] Event detail page
  - [ ] iCal download
  - [ ] Backend modules
  - [ ] Navigation (with Turbo)
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Mobile responsive testing
- [ ] No console errors
- [ ] Analytics tracking working

## Troubleshooting

### Composer Install Fails

If you get platform requirement errors:
```bash
composer install --ignore-platform-req=ext-redis
```

### Frontend Build Errors

1. Clear cache:
   ```bash
   rm -rf node_modules
   bun install
   ```

2. Check Bun version:
   ```bash
   bun --version  # Should be 1.3.9+
   ```

### TYPO3 Errors

1. Clear all caches:
   ```bash
   vendor/bin/typo3 cache:flush
   ```

2. Check database schema:
   ```bash
   vendor/bin/typo3 database:updateschema
   ```

### PHPStan Errors

If you see too many errors on first run:

1. Generate baseline:
   ```bash
   composer phpstan:baseline
   ```

2. Fix new code to standard

3. Gradually reduce baseline

## Performance Optimization

### Backend

- Use TYPO3 caching appropriately
- Optimize database queries (avoid N+1)
- Use QueryBuilder instead of raw SQL
- Enable opcode caching (OPcache)

### Frontend

- Minimize JavaScript bundle size
- Use Turbo for faster navigation
- Lazy-load images
- Enable Gzip/Brotli compression
- Use HTTP/2 or HTTP/3

## Security Best Practices

- Never commit secrets to git
- Use environment variables for credentials
- Validate all user inputs
- Use Extbase's built-in CSRF protection
- Keep dependencies updated
- Use prepared statements (QueryBuilder)
- Sanitize output in Fluid templates (auto-escaping enabled)

## Resources

- [TYPO3 Documentation](https://docs.typo3.org/)
- [Hotwired Turbo Handbook](https://turbo.hotwired.dev/handbook/introduction)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [PSR-12 Standard](https://www.php-fig.org/psr/psr-12/)

---

**Maintained by**: Markus Sommer (BeardCoder)
**Last Updated**: 2026-02-13
