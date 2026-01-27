---
name: php-modernizer
description: Modernizes PHP codebases to PHP 8.4+ standards with framework-specific optimizations. Scans projects to identify and fix overengineered code, complex conditionals, outdated patterns, and applies modern PHP features. Supports Laravel, TYPO3, Symfony with framework-aware optimizations. Use when the user requests code modernization, refactoring, simplification, or when they mention "PHP 8.4", "modernize", "simplify", "refactor", or "optimize" PHP code. Designed for Claude Code to scan entire projects and provide actionable TODO lists with batch or step-by-step execution.
---

# PHP Modernizer

Modernizes PHP codebases to leverage PHP 8.4+ features and framework-specific best practices. Reduces complexity, eliminates outdated patterns, and transforms overengineered code into clean, maintainable solutions.

## Workflow

### 1. Scan Phase
Analyze the project to identify optimization opportunities:
- Detect framework via `composer.json` (Laravel, TYPO3, Symfony)
- Scan git-tracked files (or exclude common directories if no git)
- Identify code patterns that can be modernized
- Categorize findings by type and priority

### 2. Report Phase
Generate a comprehensive TODO list showing:
- File paths and line numbers for each optimization
- Type of improvement (match expression, property promotion, etc.)
- Estimated complexity reduction
- Framework-specific optimizations when applicable

Example report format:
```
Found 23 optimization opportunities across 8 files:

app/Http/Controllers/EventController.php (5 improvements):
  ✓ Line 45-52: Replace if-else chain with match expression
  ✓ Line 78: Use constructor property promotion
  ✓ Line 102: Convert sprintf to string interpolation
  
app/Services/UserService.php (3 improvements):
  ✓ Line 23: Replace array_filter + array_map with array spread
  ✓ Line 56: Use nullsafe operator instead of null check
  
[Laravel-specific]
app/Repositories/OrderRepository.php (2 improvements):
  ✓ Line 34: Replace array operations with Collection pipeline
  ✓ Line 89: Use Eloquent whereHas instead of manual joins
```

### 3. Execute Phase
Apply optimizations with two modes:
- **All-at-once**: Apply all changes in a single operation
- **Step-by-step**: Review and apply each optimization individually

No automatic testing or commits - changes are applied directly to files.

## Framework Detection

Read `composer.json` to detect installed frameworks:
- `laravel/framework` → Laravel optimizations
- `typo3/cms-*` packages → TYPO3 optimizations
- `symfony/*` packages → Symfony optimizations
- No framework → Generic PHP modernization only

Check the `require` and `require-dev` sections for framework packages.

## File Scanning Strategy

**Preferred: Git-tracked files**
```bash
git ls-files '*.php'
```

**Fallback: Manual exclusions** (when no git repository)
Scan recursively but exclude:
- `vendor/`
- `storage/`
- `var/`
- `cache/`
- `tmp/`
- `node_modules/`
- `public/` (usually only entry points)
- `.git/`

## PHP 8.4+ Modernizations

Apply these improvements systematically. See `references/php84-features.md` for detailed patterns and examples:

1. **Match expressions** - Replace complex if-else and switch statements
2. **Property promotion** - Simplify constructor parameter assignments
3. **Readonly properties** - Add immutability where appropriate
4. **Typed properties** - Add type declarations to all properties
5. **Enums** - Replace string/int constants with proper enums
6. **Array unpacking** - Use spread operator for array operations
7. **Nullsafe operator** - Simplify null checks with `?->`
8. **Named arguments** - Improve readability for complex function calls
9. **String interpolation** - Replace sprintf with modern syntax
10. **First-class callables** - Use `fn(...)` syntax
11. **Array destructuring** - Modern array handling patterns

## Framework-Specific Optimizations

When a framework is detected via composer.json, apply framework-specific patterns:

### Laravel Optimizations
See `references/laravel-patterns.md` for complete patterns. Key improvements:
- Replace `array_*` chains with Collection methods
- Use Eloquent query builder features instead of raw queries
- Apply proper relationship patterns
- Utilize Laravel helpers appropriately

### TYPO3 Optimizations
See `references/typo3-patterns.md` for complete patterns. Key improvements:
- Modern Extbase patterns
- Fluid template optimizations
- TYPO3 API usage best practices
- Extension development patterns

### Symfony Optimizations
See `references/symfony-patterns.md` for complete patterns. Key improvements:
- Service container best practices
- Dependency injection patterns
- Controller optimizations
- Form and validation improvements

## Code Simplification Patterns

Beyond PHP version features, actively identify and fix code smells. See `references/code-smells.md` for comprehensive list:

### Complexity Reduction
- Flatten nested conditionals
- Extract complex boolean logic into named variables
- Remove unnecessary abstraction layers
- Simplify overengineered solutions

### Readability Improvements
- Remove verbose comments that state the obvious
- Use descriptive variable names
- Break down god methods into focused functions
- Apply consistent formatting

### Performance Patterns
- Eliminate unnecessary loops
- Cache repeated calculations
- Use appropriate data structures
- Optimize database queries

## Execution Guidelines

### All-at-once Mode
1. Generate complete list of changes
2. Confirm with user: "Apply all 23 optimizations?"
3. Apply all changes sequentially
4. Report completion summary

### Step-by-step Mode
1. Present first optimization with context
2. Show before/after code snippet
3. Ask: "Apply this change? (yes/no/skip remaining)"
4. Continue through remaining items
5. Track applied vs skipped changes

### Change Application
- Preserve existing formatting style where possible
- Maintain proper indentation
- Keep existing docblocks unless they're redundant
- Update imports if new classes/features are used

## Safety Considerations

**No automatic testing** - Changes are applied directly without running tests. The user is responsible for validating changes.

**No automatic commits** - No git operations are performed. The user manages version control.

**Backup recommendation** - Suggest the user commits their current state before starting if using git, or creates a backup if not.

## Usage Scripts

Use `scripts/detect_framework.py` to parse composer.json and identify frameworks.

Use `scripts/scan_files.py` to get the list of PHP files to analyze (git-aware or with exclusions).
