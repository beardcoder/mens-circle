# FrankenPHP Migration Guide

This document describes the migration from custom FrankenPHP implementation to the **serversideup/php:8.5-frankenphp** Docker image.

## Overview

The application has been refactored to use the production-ready serversideup/php FrankenPHP image instead of the base dunglas/frankenphp image. This provides:

- **Security-first design**: Runs as unprivileged `www-data` user by default
- **Production enhancements**: Built-in health checks, optimized Caddyfile, CloudFlare integration
- **Better Laravel integration**: Native Octane support with worker mode
- **Simplified configuration**: Pre-installed PHP extensions and Composer

## Key Changes

### 1. Base Image

**Before:**
```dockerfile
ARG PHP_IMAGE=dunglas/frankenphp:1-php8.5
```

**After:**
```dockerfile
ARG PHP_IMAGE=serversideup/php:8.5-frankenphp
```

### 2. Port Changes

The serversideup image uses non-privileged ports:

- **HTTP**: Port 8080 (was 8000)
- **HTTPS**: Port 8443 (was 443)

### 3. Supervisor Configuration

Supervisor is now used **only for queue workers**, not for FrankenPHP:

**Before:**
```ini
[program:frankenphp]
command=php /app/artisan octane:start --server=frankenphp --host=127.0.0.1 --port=8000
```

**After:**
- FrankenPHP program removed from supervisor
- FrankenPHP runs as the main container process via entrypoint.sh
- Supervisor manages queue workers only

**Architecture:**
1. Entrypoint runs setup tasks (migrations, cache, etc.)
2. Supervisor starts in background for queue workers
3. FrankenPHP/Octane runs as the main foreground process

### 4. Caddyfile

The custom Caddyfile has been converted to a reference/optional file. By default, Laravel Octane uses its own built-in Caddyfile that includes:

- FrankenPHP worker mode support
- Static file caching
- Compression (gzip, brotli, zstd)
- Security headers

If you need custom Caddy configuration, you can:
1. Edit `docker/frankenphp/Caddyfile`
2. Update `docker/supervisor/supervisord.conf` to include `--caddyfile=/app/Caddyfile`

### 5. Worker Configuration

FrankenPHP worker mode is now properly integrated via Laravel Octane:

- Worker file: `public/frankenphp-worker.php` (auto-created)
- Worker count: Controlled via `FRANKENPHP_WORKERS` environment variable (default: `auto`)
- Configuration: Set in `config/octane.php`

### 6. PHP Extensions

The serversideup image includes most common extensions by default. We only install what's missing:

**Pre-installed in serversideup/php:**
- composer
- pcntl
- intl
- opcache
- gd
- zip
- bcmath
- exif

**Additional installations:**
- pdo_sqlite
- pdo_pgsql
- pgsql

### 7. Docker Compose Changes

**Port mapping:**
```yaml
ports:
  - "80:8080"   # HTTP on container port 8080
  - "443:8443"  # HTTPS on container port 8443
```

**New environment variables:**
```yaml
environment:
  # Caddy/FrankenPHP Configuration
  - CADDY_SERVER_ROOT=/app/public

  # PHP Configuration
  - PHP_OPCACHE_ENABLE=1
  - PHP_MEMORY_LIMIT=256M

  # FrankenPHP Workers
  - FRANKENPHP_WORKERS=auto
```

**Updated health check:**
```yaml
healthcheck:
  test: ["CMD", "healthcheck-octane"]  # Built-in Octane health check
```

## Process Architecture

The new architecture separates concerns more clearly:

```
Container Start
    ↓
Entrypoint (entrypoint.sh)
    ├─ Database migrations (if MIGRATE_ON_START=true)
    ├─ Cache optimization (if OPTIMIZE_ON_START=true)
    ├─ Storage symlink setup
    ├─ Start Supervisor (background) → Queue Workers (2 processes)
    └─ Start FrankenPHP/Octane (foreground, PID 1) → Web Server + Worker Mode
```

**Key Points:**
- **FrankenPHP** runs as the main process (receives signals, handles container lifecycle)
- **Supervisor** runs in background managing only queue workers
- **No custom supervisor config for FrankenPHP** - it runs directly via Octane command
- Uses Laravel Octane's built-in Caddyfile with worker support

## Migration Steps

### For Existing Deployments

1. **Pull the latest changes:**
   ```bash
   git pull origin main
   ```

2. **Rebuild the Docker image:**
   ```bash
   docker compose build --no-cache
   ```

3. **Update your environment variables** (if needed):
   - No changes required for most configurations
   - Optionally set `FRANKENPHP_WORKERS` to a specific number (default: `auto`)

4. **Restart the container:**
   ```bash
   docker compose down
   docker compose up -d
   ```

5. **Verify the deployment:**
   ```bash
   # Check container logs
   docker compose logs -f app

   # Verify health endpoint
   curl http://localhost/up
   ```

### For New Deployments

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd mens-circle-2026
   ```

2. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Set required environment variables:**
   ```bash
   # Generate application key
   docker compose run --rm app php artisan key:generate
   ```

4. **Start the application:**
   ```bash
   docker compose up -d
   ```

## Worker Mode Benefits

FrankenPHP worker mode keeps your Laravel application loaded in memory, providing:

- **Faster response times**: No application bootstrap per request
- **Lower resource usage**: Persistent application state
- **Better performance**: Optimal for high-traffic applications

### Worker Configuration

**Automatic worker count (recommended for production):**
```yaml
FRANKENPHP_WORKERS=auto
```

**Manual worker count:**
```yaml
FRANKENPHP_WORKERS=4  # Adjust based on CPU cores
```

## Queue Workers

Queue workers are maintained and continue to run via Supervisor:

```ini
[program:laravel-worker]
command=php /app/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=60
numprocs=2
```

**Environment variables:**
- `QUEUE_CONNECTION=database` (default)
- Workers restart automatically on failure

## Troubleshooting

### Port conflicts

If port 80 is already in use:
```yaml
ports:
  - "8000:8080"  # Access via http://localhost:8000
```

### Custom Caddyfile not working

The application now uses Laravel Octane's built-in Caddyfile by default.

**To use a custom Caddyfile:**
1. Edit `docker/frankenphp/Caddyfile`
2. Update `docker/entrypoint.sh` line 80 to include `--caddyfile=/app/Caddyfile`
3. Rebuild the container

**Note:** Custom Caddyfiles may override Laravel Octane's worker mode configuration.

### Worker mode issues

1. **Check worker status:**
   ```bash
   docker compose exec app php artisan octane:status
   ```

2. **View FrankenPHP logs:**
   ```bash
   docker compose logs -f app | grep frankenphp
   ```

3. **Disable worker mode temporarily:**
   ```yaml
   FRANKENPHP_WORKERS=0  # Disables worker mode
   ```

### Performance issues

1. **Adjust worker count:**
   ```yaml
   FRANKENPHP_WORKERS=2  # Start low and increase
   ```

2. **Monitor memory usage:**
   ```bash
   docker stats mens-circle-app
   ```

3. **Check opcache settings:**
   - Edit `docker/php/opcache.ini`
   - Rebuild container

## Additional Resources

- [ServerSideUp FrankenPHP Documentation](https://serversideup.net/open-source/docker-php/docs/image-variations/frankenphp)
- [Laravel Octane Documentation](https://laravel.com/docs/11.x/octane)
- [FrankenPHP Worker Mode](https://frankenphp.dev/docs/worker/)

## Support

For issues or questions:
1. Check container logs: `docker compose logs -f app`
2. Verify configuration files in `docker/` directory
3. Review Octane configuration: `config/octane.php`
