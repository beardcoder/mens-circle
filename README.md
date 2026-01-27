# Männerkreis Niederbayern / Straubing

A modern web application for managing and organizing men's circle events in Lower Bavaria/Straubing, Germany. This platform enables event management, participant registration, newsletter distribution, and testimonial sharing.

## Features

### Event Management
- Create, manage, and publish events with rich content
- Support for images and detailed descriptions
- Event capacity management with automatic status updates
- Slug-based URLs for SEO-friendly event pages
- Automatic redirection to upcoming events

### Participant Registration
- Online registration form for events
- Support for single or multiple participants
- Phone number validation with German format
- Automatic confirmation emails
- Admin panel for managing registrations

### Newsletter System
- Email subscription management
- Double opt-in workflow
- Unsubscribe functionality with secure tokens
- Integration with SMTP email providers
- Newsletter campaigns with HTML templates

### Testimonials
- Public form for sharing experiences
- Optional participant approval before publication
- Rich text support for testimonials
- Display on website with author attribution

### Content Management
- Dynamic page creation and management
- Support for multiple content block types
- SEO-friendly slugs for all pages
- Dedicated pages for Impressum and Datenschutz (legal requirements)

### Admin Panel
- Powered by Filament 5 for modern admin experience
- Dashboard with statistics and overview widgets
- Health monitoring for system components
- Log viewer for debugging
- User management with authentication
- Media library integration

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3-8.5)
- **Admin Panel**: Filament 5
- **Frontend**: Livewire 3, Tailwind CSS 4
- **Build Tools**: Vite, Bun
- **Database**: SQLite (default) / MySQL
- **Server**: Laravel Octane with FrankenPHP
- **Email**: SMTP with configurable providers
- **SMS**: Seven.io API integration
- **Monitoring**: Sentry, Laravel Health, Umami Analytics (optional)
- **Media**: Spatie Media Library
- **Code Quality**: PHPStan, Laravel Pint, ECS, Rector

## Prerequisites

- PHP 8.3 or higher
- Composer
- Bun (recommended) or npm
- SQLite or MySQL database

## Installation

### Quick Setup

```bash
# Clone the repository
git clone https://github.com/beardcoder/mens-circle.git
cd mens-circle

# Run the setup script (installs dependencies, generates keys, runs migrations)
composer run setup
```

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Install JavaScript dependencies
bun install

# Build frontend assets
bun run build
```

### Configuration

Edit `.env` file and configure:
- `APP_NAME` - Your application name
- `APP_URL` - Your application URL
- Database settings (SQLite by default)
- Mail configuration (SMTP settings)
- Seven.io API key for SMS notifications
- Optional: Umami analytics, Sentry error tracking

## Development

### Start Development Server

```bash
# Start all development services at once (recommended)
composer run dev
```

This command starts:
- PHP development server (port 8000)
- Queue worker for background jobs
- Laravel Pail for real-time logs
- Schedule worker for scheduled tasks
- Vite dev server for hot module replacement

### Individual Commands

```bash
# Start web server only
php artisan serve

# Run queue worker
php artisan queue:listen

# Build frontend assets
bun run build

# Watch frontend assets for changes
bun run dev

# View logs in real-time
php artisan pail
```

### Code Quality

```bash
# Format PHP code
composer run format
# or
./vendor/bin/pint

# Static analysis
composer run lint
# or
./vendor/bin/phpstan analyse

# Refactor code with Rector
composer run rector

# Format JavaScript/TypeScript
bun run format

# Lint JavaScript/TypeScript
bun run lint

# Type check TypeScript
bun run typecheck

# Lint CSS
bun run stylelint
```

## Project Structure

```
├── app/
│   ├── Actions/          # Single-purpose action classes
│   ├── Filament/         # Admin panel resources and pages
│   ├── Http/             # Controllers, middleware, requests
│   ├── Models/           # Eloquent models
│   ├── Mail/             # Email templates
│   ├── Services/         # Business logic services
│   └── Settings/         # Application settings
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── public/               # Web server document root
├── resources/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript/TypeScript
│   └── views/            # Blade templates
├── routes/
│   ├── web.php           # Web routes
│   └── console.php       # Console commands
├── storage/              # Application storage
└── tests/                # PHPUnit/Pest tests
```

## Available Scripts

### Composer Scripts

- `composer run dev` - Start all development services
- `composer run setup` - Complete project setup
- `composer run format` - Format code with Pint
- `composer run lint` - Run PHPStan analysis
- `composer run rector` - Run Rector refactoring

### Bun/npm Scripts

- `bun run dev` - Start Vite dev server
- `bun run build` - Build for production
- `bun run lint` - Lint JavaScript/TypeScript
- `bun run format` - Format JS/TS/CSS files
- `bun run typecheck` - Run TypeScript type checking
- `bun run stylelint` - Lint CSS files

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/EventTest.php

# Run tests with coverage
php artisan test --coverage
```

## Deployment

### Production Build

```bash
# Install dependencies (production only)
composer install --no-dev --optimize-autoloader

# Build frontend assets
bun run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Server Requirements

- PHP 8.3+ with required extensions (see composer.json)
- Web server (Nginx/Apache) or FrankenPHP
- SQLite or MySQL database
- Composer
- Node.js/Bun for asset compilation

## License

MIT License. See LICENSE file for details.

## Support

For issues and questions, please use the GitHub issue tracker.

---

**Männerkreis Niederbayern / Straubing** - Building community through meaningful connections.
