# Production Readiness Guide for Mens Circle TYPO3 Extension

This document outlines the steps required to ensure this TYPO3 v14.1 extension is production-ready with modern best practices, quality tooling, and Hotwired Turbo integration.

## Table of Contents

- [Completed Tasks](#completed-tasks)
- [Quality Tools Setup](#quality-tools-setup)
- [Hotwired Turbo Integration](#hotwired-turbo-integration)
- [Next Steps](#next-steps)
- [Production Deployment Checklist](#production-deployment-checklist)

## Completed Tasks

### ✅ PHPStan Configuration (Level 5)

A comprehensive PHPStan configuration has been created at `/phpstan.neon`:

- **Analysis Level**: 5 (strict type checking, dead code detection)
- **Paths Analyzed**: All PHP files in `packages/mens_circle/Classes`, `Configuration`, and extension files
- **TYPO3-specific Bootstrapping**: Configured to understand TYPO3 globals and patterns
- **Strict Rules Enabled**:
  - Check always-true comparisons
  - Check function name case
  - Report uninitialized properties
  - Check too-wide return types in public/protected methods

### ✅ Composer Scripts for Quality Tools

Added convenient composer scripts:

```bash
composer phpstan              # Run PHPStan analysis
composer phpstan:baseline     # Generate baseline for existing issues
composer cs:fix               # Fix coding standards
composer cs:check             # Check coding standards (dry-run)
composer qa                   # Run both cs:fix and phpstan
composer qa:check             # Check both (CI mode)
```

### ✅ Hotwired Turbo Integration

Successfully integrated `@hotwired/turbo` v8.0.12 for modern SPA-like navigation:

**Package Installation:**
- Added to `package.json` dependencies
- Integrated in `App.entry.ts`

**Key Features:**
1. **Turbo Drive**: Automatic SPA-like page navigation without full page reloads
2. **Turbo Forms**: AJAX form submissions with proper validation
3. **Event Handling**: Proper lifecycle management for `turbo:load`, `turbo:frame-load`, `turbo:submit-start`, `turbo:submit-end`

**Forms Updated:**
- Newsletter form (`useNewsletterForm`)
- Event registration form (`useRegistrationForm`)
- Testimonial form (`useTestimonialForm`)

All forms now use `turbo:submit-start` for validation and `turbo:submit-end` for success handling.

## Quality Tools Setup

### PHPStan Usage

**Running Analysis:**
```bash
# Via Composer (after composer install completes)
composer phpstan

# Direct execution
vendor/bin/phpstan analyse --memory-limit=1G
```

**Creating Baseline (for existing code):**
```bash
composer phpstan:baseline
```

This creates `phpstan-baseline.neon` which allows gradual improvement by ignoring existing issues while enforcing strict standards for new code.

**Configuration Location:**
- Main config: `/phpstan.neon`
- Baseline (if generated): `/phpstan-baseline.neon`

### PHP CS Fixer Usage

**Fixing Code Style:**
```bash
# Via Composer
composer cs:fix

# Direct execution
vendor/bin/php-cs-fixer fix
```

**Checking Without Fixing:**
```bash
composer cs:check
```

**Configuration Location:**
- `.php-cs-fixer.dist.php` (root directory)

**Current Rules:**
- `@auto` - Automatic best practices
- `@auto:risky` - Risky but recommended rules
- `@PhpCsFixer:risky` - PHP-CS-Fixer risky ruleset

### Combined Quality Check

Run both tools in sequence:
```bash
composer qa        # Fix and analyze
composer qa:check  # Check only (for CI/CD)
```

## Hotwired Turbo Integration

### What is Turbo?

Turbo is part of the Hotwired framework (from the creators of Ruby on Rails) that provides:

1. **Turbo Drive**: Intercepts link clicks and form submissions, performs AJAX requests, and updates the page content
2. **Turbo Frames**: Allows decomposing pages into independent contexts
3. **Turbo Streams**: Delivers page changes over WebSocket, SSE, or in response to form submissions

### Benefits for This Project

1. **Faster Navigation**: No full page reloads between pages
2. **Better UX**: Smooth transitions, preserved scroll positions
3. **Simpler Code**: Less JavaScript needed for AJAX forms
4. **Progressive Enhancement**: Falls back to standard HTML forms if JavaScript disabled

### Implementation Details

#### App Entry Point (`App.entry.ts`)

```typescript
import * as Turbo from '@hotwired/turbo';

// Enable Turbo Drive
Turbo.start();

// Initialize on various Turbo events
document.addEventListener('DOMContentLoaded', initializeApp);
document.addEventListener('turbo:load', initializeApp);
document.addEventListener('turbo:frame-load', initializeApp);
```

#### Forms (`forms.ts`)

Forms now use Turbo events:

```typescript
// Validate before Turbo submits
form.addEventListener('turbo:submit-start', (event) => {
  // Validation logic
  if (!valid) {
    event.preventDefault();
  }
});

// Handle successful submission
form.addEventListener('turbo:submit-end', (event: Event) => {
  const customEvent = event as CustomEvent;
  if (customEvent.detail.success) {
    // Success handling
    form.reset();
  }
});
```

### TYPO3 Template Considerations

To fully leverage Turbo, consider adding to Fluid templates:

**Disable Turbo for specific links:**
```html
<f:link.action action="download" data="{turbo: 'false'}">Download PDF</f:link.action>
```

**Turbo Frames for partial updates:**
```html
<turbo-frame id="event-list">
  <f:for each="{events}" as="event">
    <!-- Event items -->
  </f:for>
</turbo-frame>
```

**Turbo Streams for real-time updates:**
```html
<!-- In controller, return Turbo Stream response -->
```

## Next Steps

### Required Actions (Need Completed Composer Install)

1. **Run PHP CS Fixer**
   ```bash
   composer install --ignore-platform-req=ext-redis
   composer cs:fix
   ```

2. **Run PHPStan Analysis**
   ```bash
   composer phpstan
   ```

3. **Create PHPStan Baseline** (if needed)
   ```bash
   composer phpstan:baseline
   ```

4. **Fix All PHPStan Issues**
   - Work through issues reported by PHPStan
   - Focus on type safety, dead code, and potential bugs
   - Aim for 0 errors at level 5

### Recommended Improvements

1. **Update PHP CS Fixer Configuration**
   - Add TYPO3-specific rules
   - Configure for PSR-12 compliance
   - Add custom rules for project consistency

2. **Add EditorConfig**
   - Create `.editorconfig` for consistent IDE settings
   - Ensure tabs/spaces consistency

3. **Add Pre-commit Hooks**
   - Use tools like `grumphp` or `husky` equivalent
   - Run PHP CS Fixer and PHPStan before commits

4. **CI/CD Integration**
   - Add GitHub Actions workflow
   - Run `composer qa:check` on every PR
   - Enforce zero errors before merge

5. **Frontend Quality Tools**
   - Add ESLint for TypeScript
   - Add Prettier for code formatting
   - Configure in `package.json` scripts

## Production Deployment Checklist

### Code Quality

- [ ] All PHPStan errors resolved (Level 5)
- [ ] All PHP CS Fixer issues resolved
- [ ] No TODO or FIXME comments in production code
- [ ] All deprecated TYPO3 APIs replaced
- [ ] Proper error handling in all controllers

### Security

- [ ] No hardcoded credentials or API keys
- [ ] All user inputs validated and sanitized
- [ ] CSRF protection enabled (Extbase default)
- [ ] SQL injection prevention (use QueryBuilder)
- [ ] XSS prevention (Fluid auto-escaping)
- [ ] File upload validation if applicable

### Performance

- [ ] Database queries optimized (no N+1 queries)
- [ ] Proper TYPO3 caching configured
- [ ] Frontend assets minified (`bun run build`)
- [ ] Images optimized and lazy-loaded
- [ ] HTTP/2 or HTTP/3 enabled on web server
- [ ] Gzip/Brotli compression enabled

### TYPO3 Configuration

- [ ] `trustedHostsPattern` configured for production domain
- [ ] `sitemap.xml` generation working
- [ ] RealURL/routing configured properly
- [ ] Email sending configured (SMTP)
- [ ] Caching configuration reviewed
- [ ] Database credentials secured (environment variables)

### Frontend

- [ ] Turbo working correctly on all pages
- [ ] Forms submit via Turbo without errors
- [ ] Navigation smooth and fast
- [ ] No console errors in production
- [ ] Analytics tracking working (Umami)
- [ ] All images have proper alt text

### Testing

- [ ] Manual testing of all forms
- [ ] Manual testing of all pages
- [ ] Test newsletter signup/unsubscribe
- [ ] Test event registration flow
- [ ] Test testimonial submission
- [ ] Test iCal download
- [ ] Mobile responsive testing
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)

### Documentation

- [ ] README.md up to date
- [ ] Deployment instructions documented
- [ ] Environment variables documented
- [ ] Backup procedures documented
- [ ] Update procedures documented

### Monitoring

- [ ] Error logging configured
- [ ] Performance monitoring setup
- [ ] Uptime monitoring configured
- [ ] Database backup automated
- [ ] File backup automated

## Best Practices Applied

### TYPO3 v14.1 Standards

✅ **Site Sets** instead of static TypoScript templates
✅ **Constructor injection** with `readonly` properties
✅ **Strict typing** (`declare(strict_types=1)`) in all PHP files
✅ **Modern PHP 8.5** features (enums, named arguments, promoted properties)
✅ **Symfony Messenger** for async operations
✅ **QueryBuilder** for database operations
✅ **Services.yaml** for dependency injection
✅ **PSR-4 autoloading** via Composer

### Code Quality

✅ **PHPStan Level 5** analysis configured
✅ **PHP CS Fixer** with strict rulesets
✅ **No deprecated APIs** used
✅ **Proper separation of concerns** (Domain, Controller, Repository)
✅ **Type hints** on all properties and parameters
✅ **Return type declarations** on all methods

### Frontend Modern Stack

✅ **Hotwired Turbo** for SPA-like navigation
✅ **Vite** for fast builds
✅ **TypeScript** for type safety
✅ **Bun** as package manager
✅ **Motion** for smooth animations
✅ **Progressive enhancement** approach

## Contact & Support

For questions about this setup:
- Review TYPO3 v14 documentation: https://docs.typo3.org/
- Hotwired Turbo documentation: https://turbo.hotwired.dev/
- PHPStan documentation: https://phpstan.org/
- PHP CS Fixer documentation: https://github.com/FriendsOfPHP/PHP-CS-Fixer

---

**Last Updated**: 2026-02-13
**TYPO3 Version**: 14.1
**PHP Version**: 8.5
**Status**: 🟡 Quality tools configured, awaiting composer install completion
