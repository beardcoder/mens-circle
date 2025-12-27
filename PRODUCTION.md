# Production Deployment Guide
## Männerkreis Straubing Website

This guide outlines all steps necessary to deploy the application to production with Laravel Octane and FrankenPHP.

---

## Pre-Deployment Checklist

### 1. Environment Configuration

Update your `.env` file for production:

```bash
APP_NAME="Männerkreis Straubing"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mens-circle.de

# Database
DB_CONNECTION=sqlite

# Cache
CACHE_STORE=database

# Sessions
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hallo@mens-circle.de"
MAIL_FROM_NAME="${APP_NAME}"

# Octane
OCTANE_SERVER=frankenphp
OCTANE_HTTPS=true

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 2. Laravel Optimizations

Run these commands before deployment:

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan responsecache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force

# Link storage
php artisan storage:link
```

### 3. File Permissions

Ensure proper permissions for Laravel:

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Deployment with FrankenPHP + Octane

### 1. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### 2. Start Octane Server

For production, use a process manager like systemd:

**Create systemd service file:** `/etc/systemd/system/octane.service`

```ini
[Unit]
Description=Laravel Octane Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/mens-circle-design
ExecStart=/usr/bin/php /path/to/mens-circle-design/artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

**Enable and start the service:**

```bash
sudo systemctl daemon-reload
sudo systemctl enable octane
sudo systemctl start octane
sudo systemctl status octane
```

### 3. Nginx Reverse Proxy (Optional but Recommended)

Configure Nginx as a reverse proxy to Octane:

**Nginx config:** `/etc/nginx/sites-available/mens-circle`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name mens-circle.de www.mens-circle.de;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name mens-circle.de www.mens-circle.de;

    # SSL Configuration (use Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/mens-circle.de/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mens-circle.de/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    # Logging
    access_log /var/log/nginx/mens-circle-access.log;
    error_log /var/log/nginx/mens-circle-error.log;

    # Proxy to Octane
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 60s;
    }
}
```

**Enable the site:**

```bash
sudo ln -s /etc/nginx/sites-available/mens-circle /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. SSL Certificate with Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d mens-circle.de -d www.mens-circle.de
```

---

## Performance Optimizations

### 1. Caching Strategy

The application implements:

- **Page caching** - 1 hour TTL for pages
- **Event caching** - 10 minutes TTL for events
- **Cache invalidation** - Automatic via observers

Cache is stored in database (SQLite) for simplicity.

### 2. Database Optimizations

SQLite is optimized for read-heavy workloads:

```sql
-- Already implemented in migrations
CREATE INDEX idx_pages_slug ON pages(slug);
CREATE INDEX idx_events_published ON events(is_published, event_date);
CREATE INDEX idx_newsletter_email ON newsletter_subscriptions(email);
```

### 3. Asset Optimization

Vite automatically optimizes assets in production:

```bash
npm run build
```

This will:
- Minify CSS and JavaScript
- Generate hashed filenames for cache busting
- Optimize images

---

## Monitoring & Maintenance

### 1. Log Monitoring

Monitor Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Monitor Nginx logs:

```bash
tail -f /var/log/nginx/mens-circle-error.log
```

### 2. Octane Management

Restart Octane after code changes:

```bash
sudo systemctl restart octane
```

Or use the graceful reload:

```bash
php artisan octane:reload
```

### 3. Database Backups

Backup SQLite database regularly:

```bash
#!/bin/bash
# backup-database.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
DB_PATH="/path/to/mens-circle-design/database/database.sqlite"

cp "$DB_PATH" "$BACKUP_DIR/database_$DATE.sqlite"

# Keep only last 30 days
find "$BACKUP_DIR" -name "database_*.sqlite" -mtime +30 -delete
```

Add to crontab:

```bash
0 2 * * * /path/to/backup-database.sh
```

---

## SEO Features Implemented

### 1. Meta Tags
- Dynamic title and description per page
- Open Graph tags for social sharing
- Twitter card tags
- Canonical URLs

### 2. Structured Data (JSON-LD)
- Organization schema on homepage
- Event schema on event pages

### 3. Sitemap & Robots
- Dynamic XML sitemap: `/sitemap.xml`
- Robots.txt: `/robots.txt`

### 4. Submit to Search Engines

After deployment:

```bash
# Google Search Console
# Submit sitemap: https://mens-circle.de/sitemap.xml

# Bing Webmaster Tools
# Submit sitemap: https://mens-circle.de/sitemap.xml
```

---

## Email Configuration

### Production SMTP Settings

Use a reliable SMTP service (e.g., SendGrid, Mailgun, or your hosting provider):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

### Email Templates

Two styled email templates are implemented:

1. **Event Registration Confirmation** - Sent after successful event registration
2. **Newsletter Welcome** - Sent after newsletter subscription

Both use Laravel Markdown templates for easy styling.

---

## Security Checklist

- [x] `APP_DEBUG=false` in production
- [x] CSRF protection on all forms
- [x] HTTPS enforced via Nginx
- [x] Security headers configured
- [x] File upload validation
- [x] SQL injection prevention (Eloquent ORM)
- [x] XSS protection (Blade escaping)
- [ ] Rate limiting on public endpoints (optional, configure in `app/Http/Kernel.php`)
- [ ] Regular dependency updates: `composer update`

---

## Queue Workers (Optional)

If you want to queue emails for better performance:

1. Update `.env`:
```env
QUEUE_CONNECTION=database
```

2. Create systemd service for queue worker:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/mens-circle-design
ExecStart=/usr/bin/php /path/to/mens-circle-design/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

3. Update mail classes to implement `ShouldQueue`:

```php
class EventRegistrationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    // ...
}
```

---

## Troubleshooting

### Issue: Octane won't start

**Solution:**
```bash
# Check for port conflicts
sudo lsof -i :8000

# Check FrankenPHP binary
ls -la vendor/laravel/octane/bin/frankenphp

# Check logs
php artisan octane:start --watch
```

### Issue: Cache not clearing

**Solution:**
```bash
# Manually clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan responsecache:clear

# Restart Octane
sudo systemctl restart octane
```

### Issue: Emails not sending

**Solution:**
```bash
# Test mail configuration
php artisan tinker
> Mail::raw('Test', fn($msg) => $msg->to('test@example.com')->subject('Test'));

# Check mail logs
tail -f storage/logs/laravel.log
```

---

## Deployment Automation

Create a deployment script: `deploy.sh`

```bash
#!/bin/bash
set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan responsecache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart Octane
sudo systemctl restart octane

echo "Deployment complete!"
```

Make it executable:
```bash
chmod +x deploy.sh
```

---

## Performance Benchmarks

Expected performance with Octane + FrankenPHP:

- **Homepage:** < 50ms response time
- **Event page:** < 100ms response time (with cache)
- **API endpoints:** < 200ms response time
- **Concurrent users:** 500+ (depending on server resources)

Monitor with:
```bash
ab -n 1000 -c 100 https://mens-circle.de/
```

---

## Support & Maintenance

### Regular Tasks

**Weekly:**
- Monitor error logs
- Check disk space
- Review analytics

**Monthly:**
- Update dependencies: `composer update`
- Review security advisories
- Test backup restoration

**Quarterly:**
- Performance audit
- SEO review
- User feedback review

---

## Conclusion

Your Männerkreis Straubing website is now production-ready with:

- ✅ Laravel Octane + FrankenPHP for high performance
- ✅ Comprehensive caching strategy
- ✅ SEO optimizations (meta tags, sitemap, structured data)
- ✅ Styled email templates
- ✅ Production configuration optimizations
- ✅ Security best practices
- ✅ Monitoring and maintenance guidelines

For questions or issues, refer to:
- Laravel Documentation: https://laravel.com/docs
- Octane Documentation: https://laravel.com/docs/octane
- FrankenPHP Documentation: https://frankenphp.dev

---

**Last Updated:** December 2024
