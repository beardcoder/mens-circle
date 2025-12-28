# Go-Live Checklist

Vor dem Produktivstart **MÜSSEN** folgende Punkte abgeschlossen sein:

## 🔴 KRITISCH - Muss vor Go-Live erledigt sein

### 1. Social Media OG-Image erstellen
- [ ] Erstelle ein Bild in der Größe **1200x630px**
- [ ] Speichere es als `/public/images/og-image.jpg`
- [ ] Empfohlener Inhalt: Logo + "Männerkreis Straubing" + Tagline
- [ ] Teste mit Facebook Sharing Debugger: https://developers.facebook.com/tools/debug/

### 2. Impressum vervollständigen
- [ ] Öffne `resources/views/impressum.blade.php`
- [ ] Ersetze die Musteradresse mit der echten Adresse
- [ ] Entferne die Warnung "WICHTIG: Vor Go-Live vervollständigen!"
- [ ] **Rechtlich zwingend erforderlich nach § 5 TMG!**

### 3. Produktionsserver vorbereiten
- [ ] `.env` File mit Produktionswerten erstellen (siehe `.env.example`)
- [ ] `APP_ENV=production` setzen
- [ ] `APP_DEBUG=false` setzen
- [ ] `APP_URL` auf echte Domain setzen
- [ ] Mail-Server konfigurieren (SMTP Zugangsdaten)

### 4. Assets bauen
```bash
bun install
bun run build
```

### 5. Datenbank & Caches
```bash
php artisan migrate --force
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Admin-User erstellen
```bash
php artisan make:filament-user
```

### 7. Queue Worker einrichten
- [ ] Systemd/Supervisor Service für `php artisan queue:work` erstellen
- [ ] Service starten und aktivieren

### 8. Berechtigungen setzen
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## ⚠️ EMPFOHLEN

### 9. SSL-Zertifikat
- [ ] Let's Encrypt Zertifikat installieren
- [ ] HTTPS erzwingen (Redirect in Webserver-Config)

### 10. Erste Inhalte erstellen
- [ ] Mindestens ein Event über Admin-Panel erstellen
- [ ] Event veröffentlichen

### 11. E-Mail-Tests durchführen
- [ ] Newsletter-Willkommensmail testen
- [ ] Event-Bestätigungsmail testen
- [ ] Abmelde-Link testen

### 12. Browser-Tests
- [ ] Desktop: Chrome, Firefox, Safari
- [ ] Mobile: iOS Safari, Chrome Android
- [ ] Responsive Design prüfen

## 📋 NACH GO-LIVE

### 13. Monitoring einrichten
- [ ] Error Logging prüfen
- [ ] Queue Worker überwachen
- [ ] Backup-Strategie für Datenbank

### 14. SEO
- [ ] Google Search Console einrichten
- [ ] Sitemap bei Google einreichen
- [ ] robots.txt prüfen

## ✅ FERTIG

Wenn alle Punkte abgehakt sind, ist die Anwendung bereit für den Go-Live!
