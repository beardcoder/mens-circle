# Setup Instructions

## Storage Permission Fix

If you encounter errors like:
- `ERROR could not clean default/global storage`
- `ERROR unable to autosave config`

This is due to storage permissions and cache configuration issues. Follow these steps:

### 1. Install Dependencies

```bash
composer install --no-interaction
```

### 2. Set Up Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Cache Driver

Edit `.env` and change the following settings:

```env
# Change from database to file (SQLite PDO not required)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 4. Set Storage Permissions

```bash
chmod -R 777 storage bootstrap/cache
```

### 5. Create Database File (if using SQLite)

```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
```

### 6. Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 7. Run Development Server

```bash
composer run dev
```

## Root Cause

The errors occur when:
1. The `storage/` and `bootstrap/cache/` directories lack write permissions
2. The cache is configured to use `database` driver but SQLite PDO extension is not installed
3. The `.env` file doesn't exist

The fix involves switching to file-based caching and ensuring proper directory permissions.
