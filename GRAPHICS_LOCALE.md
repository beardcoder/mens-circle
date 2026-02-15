# GraphicsMagick and Locale Configuration

This document explains the GraphicsMagick and German locale setup for the Männerkreis TYPO3 v14 project.

## Image Processing

### GraphicsMagick
GraphicsMagick is installed for high-performance image processing in TYPO3. It provides:
- Image resizing and thumbnails
- Format conversion
- Image optimization
- Better performance compared to GD for batch operations

**Production**: Installed via Dockerfile using `apt-get install graphicsmagick`
**Development**: Installed via DDEV web-build customization (`.ddev/web-build/Dockerfile.typo3`)

### PHP Extensions
The following PHP extensions are installed for image processing:
- `intl` - Internationalization functions
- `gd` - GD graphics library
- `exif` - Read EXIF data from images
- `imagick` - ImageMagick extension (uses GraphicsMagick as backend)

## Locale Configuration

### German Locale (de_DE.UTF-8)
The German locale is installed and configured as the default system locale:

**Production (Dockerfile)**:
- Locale package installed
- `de_DE.UTF-8` generated via `locale-gen`
- Environment variables: `LANG`, `LANGUAGE`, `LC_ALL` set to `de_DE.UTF-8`

**Development (DDEV)**:
- Locale configured in `.ddev/web-build/Dockerfile.typo3`
- Environment variables added to `.ddev/config.yaml`
- Timezone set to `Europe/Berlin`
- Note: Locale environment variables are set in both the Dockerfile (container defaults) and config.yaml (DDEV process environment) to ensure proper propagation

**TYPO3 Site Configuration**:
The site is already configured with German as the default language in `config/sites/mens-circle/config.yaml`:
```yaml
languages:
  - title: Deutsch
    enabled: true
    languageId: 0
    typo3Language: de
    locale: de_DE.UTF-8
    iso-639-1: de
    hreflang: de-DE
```

## Testing

### Automated Verification Script
Run the included verification script to check all components:

```bash
# For DDEV (default)
./verify-setup.sh

# For production Docker container
CONTAINER_ID=<your-container-id> ./verify-setup.sh docker
```

**Note**: The script is already executable. If you need to set permissions manually:
```bash
chmod +x verify-setup.sh
```

The script checks:
- GraphicsMagick installation and version
- PHP extensions (gd, exif, imagick, intl)
- German locale availability (de_DE.UTF-8)
- Locale environment variables (LANG, LANGUAGE, LC_ALL)
- PHP timezone setting

### Manual Verification

### Verify GraphicsMagick Installation
```bash
# Production container
docker exec -it <container> gm version

# DDEV
ddev exec gm version
```

### Verify Locale
```bash
# Production container
docker exec -it <container> locale

# DDEV
ddev exec locale
```

Expected output should include:
```
LANG=de_DE.UTF-8
LANGUAGE=de_DE:de
LC_ALL=de_DE.UTF-8
```

### Verify PHP Extensions
```bash
# DDEV
ddev exec php -m | grep -E "(gd|exif|imagick|intl)"
```

## Rebuilding

After changes to the Dockerfile or DDEV configuration:

**Production**:
```bash
docker build -t mens-circle .
```

**Development**:
```bash
ddev restart
```

The DDEV web-build customization will automatically rebuild on restart.
