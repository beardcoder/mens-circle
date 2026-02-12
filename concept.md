# Webseiten- & Webapp-Konzept
## Männerkreis Niederbayern / Straubing

> Ziel: Eine SEO-starke Website **mit integrierter Webanwendung** zur Organisation von Männerkreis-Veranstaltungen: Events verwalten, Teilnehmer anmelden, Newsletter versenden, Erfahrungsberichte sammeln – inkl. Admin-Bereich und DSGVO-konformer Prozesse.

---

## 1) Produktziel & Nutzen

### Zweck
Die Plattform dient als zentrale Anlaufstelle für den Männerkreis in Niederbayern/Straubing:
- **Öffentliche Website**: Informationen, Termine, Anmeldung, Testimonials, Inhalte (CMS), Rechtstexte
- **Admin-Webapp**: Verwaltung von Events, Registrierungen, Newsletter, Testimonials, Seiteninhalten, Systemstatus

### Primäre Zielgruppen
- **Interessenten/Teilnehmer**: Event finden → anmelden → Infos erhalten → ggf. Newsletter abonnieren
- **Orga-Team/Admins**: Events pflegen, Teilnehmerlisten verwalten, Newsletter versenden, Inhalte veröffentlichen

### Erfolgskennzahlen (Beispiele)
- Anzahl Anmeldungen pro Event / Auslastung
- Newsletter-Abonnenten (confirmed) & Abmeldequote
- Conversion: Event-Detailseite → Anmeldung
- Reduzierter Orga-Aufwand (automatisierte Mails, Status, Export)

---

## 2) Informationsarchitektur (Website)

### Öffentliche Bereiche (Navigation)
1. **Startseite**
    - Kurzer Einstieg: Was ist der Männerkreis?
    - Nächstes Event (Teaser)
    - Call-to-Action: „Zum nächsten Kreis anmelden“
    - Testimonials (Auszug)
2. **Events**
    - Liste kommender Events (Default)
    - Filter: kommende / vergangene
    - Event-Detailseite mit Anmeldung
3. **Über den Männerkreis**
    - Haltung, Ablauf, Werte
    - Für wen geeignet / nicht geeignet
4. **Testimonials**
    - Liste freigegebener Erfahrungsberichte
    - Link: „Erfahrung teilen“
5. **Newsletter**
    - Abo-Formular (Double Opt-In)
    - Erwartungsmanagement: Frequenz, Inhalte
6. **Kontakt**
    - Kontaktformular oder Mailadresse
    - Optional: WhatsApp/Telefon nur, wenn gewünscht
7. **Rechtliches (Footer)**
    - **Impressum**
    - **Datenschutzerklärung**
    - Optional: AGB/Teilnahmebedingungen

### System-Redirects & UX-Regeln
- `/events` leitet standardmäßig auf **kommende Events** weiter
- Wenn keine kommenden Events existieren: Hinweis + Newsletter-CTA + „Vergangene Events ansehen“
- Auf Event-Detailseite:
    - Anzeige: **freie Plätze** oder **ausgebucht**
    - Bei ausgebucht: optional Warteliste (nur falls gewünscht) – ansonsten Newsletter-CTA

---

## 3) Kernfunktionen (Webapp)

## 3.1 Event-Management (Admin)
**Ziel:** Events einfach erstellen, veröffentlichen, verwalten.

### Funktionen
- CRUD für Veranstaltungen
- SEO-freundliche URLs via **Slug**
- Bildupload (Hero/Teaser)
- Status: `draft` / `published`
- Soft-Delete & Wiederherstellung
- Auto-Status „ausgebucht“ bei Erreichen der Kapazität
- Anzeige: verfügbare Plätze, Teilnehmerzahl, Registrierungsstatus

### Öffentliche Darstellung (Event-Detail)
- Titel, Datum, Uhrzeit, Ort, Beschreibung, Bild
- freie Plätze / ausgebucht
- CTA: „Jetzt anmelden“
- Hinweise: Spendenbasis, Datenschutzlink, Kontakt

---

## 3.2 Teilnehmer-Registrierung
**Ziel:** Reibungslose, DSGVO-konforme Anmeldung.

### Registrierung (Public)
- Anmeldung pro Event
- Einzel- oder Mehrfach-Teilnehmer (z. B. „ich +1“)

### Felder (pro Teilnehmer)
- `first_name` (Pflicht)
- `last_name` (Pflicht)
- `email` (Pflicht)
- `phone` (Pflicht, deutsches Format)
- `privacy_consent` (Pflicht-Checkbox, nicht vorausgewählt)
- optional: `note` (z. B. Hinweis an Orga)

### Regeln & Validierung
- **Client-seitig**: Pflichtfelder, E-Mail Format, Telefonnummernformat
- **Server-seitig**:
    - E-Mail Format + Normalisierung (lowercase, trim)
    - Telefon normalisieren (E.164 bevorzugt)
    - Double-Submit-Schutz
    - **Doppelregistrierung verhindern**: 1 Teilnehmer (E-Mail) pro Event
        - Bei Mehrfachteilnehmern: pro Person eigene E-Mail ODER explizit erlaubte „+1“-Logik (siehe unten)

### Status-Tracking Registrierung
- `status`: `confirmed` (Default nach erfolgreicher Erstellung)
- Optional: `cancelled` (falls Admin stornieren kann)
- Zeitstempel: `created_at`, `confirmed_at`

### E-Mails
- Automatische Bestätigungs-E-Mail nach Registrierung (asynchron via Queue)
- Optional: SMS-Bestätigung (asynchron)

---

## 3.3 Newsletter-System
**Ziel:** Rechtssicheres E-Mail-Marketing (Double Opt-In) + Kampagnenversand.

### Abonnement (Public)
- Feld: `email` (Pflicht)
- Status:
    - `pending` (nach Eintragung)
    - `confirmed` (nach Klick)
    - `unsubscribed` (nach Abmeldung)
- Token-Workflow:
    - `confirm_token` (sicher, eindeutig, expirable)
    - `unsubscribe_token` (sicher, eindeutig)
- Zeitstempel: `requested_at`, `confirmed_at`, `unsubscribed_at`

### Kampagnen (Admin)
- Versand:
    - Empfängerliste = `confirmed` & `active`
    - Versand in Batches über Queue
    - Personalisierter Unsubscribe-Link pro Empfänger

---

## 3.4 Testimonials (Erfahrungsberichte)
**Ziel:** Vertrauen aufbauen, moderiert veröffentlichen.

### Öffentliches Formular
- `content` (Zitat/Erfahrung, Rich Text oder Plain + Formatierung)
- `author_name` (optional)
- `author_role` (optional)
- `email` (Pflicht für Rückfragen, nicht öffentlich)
- Zustimmung/Info: „Wir veröffentlichen nur nach Freigabe“

### Moderation (Admin)
- Standard: `is_published = false`
- Admin kann:
    - veröffentlichen (setzt `published_at`)
    - ablehnen (Soft-Delete oder „rejected“-Status)
    - bearbeiten (mit interner Notiz)
    - Sortierung festlegen (`sort_order`)

### Öffentliche Darstellung
- Zitat + optional Name/Rolle
- Keine öffentliche E-Mail

---

## 3.5 CMS (Seiten & Inhalte)
**Ziel:** Inhalte ohne Entwickler pflegen, SEO sauber.

### Funktionen
- Seiten erstellen/bearbeiten
- Slug-basierte URLs: `/pages/{slug}` oder direkt `/ {slug}` (empfohlen: direkt, wenn sauber geplant)
- Meta-Daten:
    - `meta_title`
    - `meta_description`
    - optional: OG Title/Description/Image
- Status: `draft|published`
- Versionierung: mindestens Zeitstempel + optional „Revision-History“

### Content-Blocks (flexibel)
Beispiele:
- Hero (Titel, Text, Bild, CTA)
- Rich Text
- FAQ
- Quote/Testimonial-Block
- Image + Text
- Call-to-Action (Newsletter / Event)
- Divider/Spacer

### Pflichtseiten (DE)
- Impressum (eigene CMS-Seite, aber unveröffentlichbar = false)
- Datenschutzerklärung (eigene CMS-Seite)

---

## 4) Admin-Panel (Module)

### 4.1 Dashboard (Widgets)
- Kommende Events (Anzahl + nächste Termine)
- Registrierungen (gesamt + pro Event)
- Newsletter-Abonnenten (confirmed)
- Ausstehende Testimonials (pending)

### 4.2 Events
- Liste: Draft/Published, kommende/vergangene
- Detail:
    - Teilnehmerliste
    - Kapazitätsanzeige
    - Export (CSV)
    - Manuelle Statusaktionen (optional)

### 4.3 Registrierungen
- Suchen/Filtern (Event, Status, Datum)
- Status ändern (optional)
- Export (CSV/Excel)

### 4.4 Newsletter
- Abonnentenliste (Filter: pending/confirmed/unsubscribed)
- Kampagnen:
    - erstellen, testen, versenden
    - Versandhistorie
    - Empfängeranzahl

### 4.5 Testimonials
- Inbox (pending)
- Freigabe/Ablehnung
- Sortierung
- Soft-Delete/Archiv

### 4.6 CMS
- Seitenliste
- Editor (Blocks)
- Media Library (Uploads, Bilder)

### 4.8 Benutzerverwaltung
- Admin-Accounts
- Rollen:
    - `admin`
    - optional: `editor` (CMS/Testimonials ohne Systemrechte)

---

## 5) Workflows (End-to-End)

## 5.1 Event-Registrierung (Public)
1. Nutzer öffnet Event-Liste → wählt Event
2. System prüft: Kapazität erreicht?
    - ja → „ausgebucht“ + Alternativen
3. Nutzer füllt Formular aus + Datenschutz-Checkbox
4. Client-Validierung
5. Server-Validierung:
    - Doppelregistrierung (E-Mail + Event)
    - Kapazität final prüfen (Race-Condition-safe)
6. Registrierung speichern, Zähler aktualisieren
7. Bestätigungsmail via Queue
8. Erfolgsseite

## 5.2 Newsletter Double Opt-In
1. Nutzer trägt E-Mail ein
2. System erstellt Abo `pending` + Token
3. Bestätigungsmail
4. Nutzer klickt Link
5. System validiert Token → `confirmed`, Timestamp
6. Willkommensmail (optional)
7. Erfolgsseite

## 5.3 Newsletter Abmeldung
1. Unsubscribe-Link mit Token
2. System validiert Token → `unsubscribed`
3. Erfolgsseite (optional Bestätigungsmail)

## 5.4 Newsletter Versand
1. Admin erstellt Kampagne (`draft`)
2. Test/Preview
3. Start Versand (`sending`)
4. Queue-Batches an confirmed-Abonnenten
5. SMTP Versand
6. Kampagne `sent`, `sent_at`, Empfängeranzahl
7. Admin erhält Summary

## 5.5 Testimonial
1. Nutzer sendet Testimonial
2. Speichern `is_published=false`
3. Admin sieht pending im Dashboard
4. Admin veröffentlicht/ablehnt/bearbeitet
5. Bei Veröffentlichung erscheint es auf Website
6. Optional: Dankesmail

## 5.6 Event Lifecycle
1. Admin erstellt Event (`draft`)
2. Konfiguriert Inhalt, Ort, Kapazität, Bild
3. Publish → sichtbar
4. Registrierungen laufen
5. Auto „ausgebucht“ bei max Teilnehmer
6. Event findet statt → bleibt als „vergangen“ sichtbar
7. Optional: Archiv/Soft-Delete

---

## 6) E-Mail-Templates (Pflicht-Set)

### Event: Registrierungsbestätigung
- Eventtitel, Datum, Zeit, Ort, Anfahrt/Link
- Teilnehmerdaten
- Kontakt
- Footer: Impressum/Datenschutz

### Newsletter: Double Opt-In
- Bestätigungslink mit Token
- Datenschutzhinweis
- Footer: Impressum/Datenschutz

### Newsletter: Kampagne
- HTML Inhalt
- Personalisierter Unsubscribe-Link (Token)
- Footer: Impressum/Datenschutz

### Admin: Neue Registrierung
- Teilnehmerdaten + Eventinfos
- Link ins Admin-Panel

### Admin: Neues Testimonial
- Inhalt + Link zur Freigabe

---

## 7) Technische Anforderungen (Framework-agnostic)

## 7.1 Backend
- Architektur: REST API + SSR Frontend **oder** MVC mit SSR
- ORM/DB Layer
- Queue-System (E-Mail/SMS/Newsletter-Batches)
- SMTP Integration
- Optional SMS Provider (z. B. Seven.io)
- File Storage (lokal + optional Cloud)
- Bildoptimierung (Resize, WebP/AVIF optional)
- Caching (z. B. Seiten/Fragments, Eventliste)
- Auth:
    - Admin Login/Logout
    - Sessions
    - Rollen & Rechte

## 7.2 Frontend
- **SSR für SEO**
- Responsive (mobile-first)
- Moderne CSS Features:
    - CSS Nesting
    - Custom Properties
    - OKLCH
    - Container Queries
    - Logical Properties
- Form Handling:
    - Client Validierung
    - Inline Fehler
    - AJAX Submit (optional, mit SSR-Fallback)
- Build Tool:
    - HMR
    - Bundling/Minify

---

## 8) SEO-Anforderungen
- Slug-URLs:
    - `/events/{slug}`
    - `/pages/{slug}` oder Direkt-Routen
- Dynamische Meta-Tags (Title, Description)
- Open Graph + Twitter Cards
- JSON-LD:
    - `Event`
    - `Organization`
- Sitemap automatisch
- Robots.txt konfigurierbar
- Canonical URLs

---

## 9) Rechtliches & DSGVO (DE)

### Registrierung
- Checkbox Datenschutz (Pflicht, nicht vorangekreuzt)
- Link zur Datenschutzerklärung
- Speicherung von `consent_timestamp`

### Newsletter
- **Double Opt-In** Pflicht
- One-Click Unsubscribe in jeder Mail
- Token-basiert, sicher & eindeutig

### Pflichtseiten
- Impressum
- Datenschutzerklärung
- Gut sichtbar im Footer

### Betroffenenrechte
- Datenauskunft / Datenlöschung auf Anfrage
- Soft-Deletes zur Compliance + Wiederherstellbarkeit
- Prozesse dokumentierbar (Audit-Trail optional)

---

## 10) Datenmodelle (kurz & umsetzungsnah)

### Event
- id, title, slug, description, date, start_time, end_time
- location_name, street, zip, city
- max_participants, price_note, image
- status, created_at, updated_at, deleted_at

### Registration
- id, event_id
- first_name, last_name, email, phone
- status, consent_timestamp
- created_at, updated_at, deleted_at

### NewsletterSubscription
- id, email
- status (pending/confirmed/unsubscribed)
- confirm_token, unsubscribe_token
- requested_at, confirmed_at, unsubscribed_at

### NewsletterCampaign
- id, subject, html_body
- status (draft/sending/sent)
- sent_at, recipients_count, created_at

### Testimonial
- id, content, author_name?, author_role?, email
- is_published, published_at, sort_order
- created_at, updated_at, deleted_at

### Page (CMS)
- id, title, slug, blocks(json)
- meta_title, meta_description, og_image?
- status (draft/published)
- created_at, updated_at

---

## 11) Offene Optionen (bewusst als „optional“ markiert)
- Warteliste bei ausgebuchten Events
- SMS-Bestätigung
- Analytics (Umami) inkl. Conversion Tracking
- Sentry/Monitoring Integration
- Payment (nicht empfohlen, da „Spendenbasis“ – lieber Hinweistext)

---

## 12) Akzeptanzkriterien (MVP)
- Öffentliche Website mit: Start, Events, Event-Detail+Anmeldung, Testimonials, Newsletter, CMS-Seiten, Rechtliches
- Admin: Events CRUD, Registrierungen Liste+Export, Newsletter DOI + Kampagnenversand, Testimonials Moderation, CMS Seiten
- SEO: SSR, Slugs, Meta, Sitemap, JSON-LD Event
- DSGVO: Checkbox+Timestamp, DOI, Unsubscribe, Pflichtseiten

