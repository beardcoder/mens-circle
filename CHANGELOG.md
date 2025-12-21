# Changelog - Go-Live Vorbereitung

## 2025-12-21 - Pre-Launch Fixes

### ✅ Configuration
- **Fixed**: Timezone changed from UTC to Europe/Berlin
- **Fixed**: Locale changed from English (en) to German (de)
- **Updated**: `.env.example` configured for production deployment
- **Updated**: Mail configuration with production-ready values

### ✅ Error Handling
- **Added**: Custom error pages (404, 500, 503) with German translations
- **Added**: Try-catch blocks for email sending in EventController
- **Added**: Try-catch blocks for email sending in NewsletterController
- **Added**: Comprehensive error handling in SendNewsletterJob
- **Added**: Retry logic (3 attempts, 60s backoff) for newsletter sending
- **Added**: Failed job handler to reset newsletter status

### ✅ Code Quality
- **Created**: EventRegistrationRequest Form Request class
- **Created**: NewsletterSubscriptionRequest Form Request class
- **Improved**: Validation now follows Laravel best practices

### ✅ SEO & Standards
- **Fixed**: Sitemap lastmod format changed from Atom to W3C (toW3cString)
- **Improved**: W3C compliance for sitemap.xml

### ✅ Documentation
- **Added**: GO-LIVE-CHECKLIST.md with complete deployment guide
- **Added**: CHANGELOG.md to track all changes
- **Updated**: Impressum with placeholder warning

### 🔴 PENDING - Required Before Go-Live
1. Create OG-Image: `/public/images/og-image.jpg` (1200x630px)
2. Complete Impressum with real address
3. Configure production `.env` file
4. Build frontend assets: `npm run build`
5. Set up queue worker as system service

## Notes
All critical bugs and configuration issues have been resolved. The application is code-complete and ready for deployment once the pending items are addressed.
