-- Initial schema for the Männerkreis Go application.
-- Clean, normalised rebuild of the Laravel data model.

-- Administrators (admin panel access).
CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT NOT NULL,
    email           TEXT NOT NULL UNIQUE,
    email_verified_at DATETIME,
    password        TEXT NOT NULL DEFAULT '',
    github_id       TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- A person: the hub for registrations and newsletter subscriptions.
CREATE TABLE IF NOT EXISTS participants (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name      TEXT,
    last_name       TEXT,
    email           TEXT NOT NULL UNIQUE,
    phone           TEXT,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Men's circle gatherings.
CREATE TABLE IF NOT EXISTS events (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    title            TEXT NOT NULL,
    slug             TEXT NOT NULL UNIQUE,
    description      TEXT,
    event_date       DATETIME NOT NULL,
    start_time       TEXT NOT NULL,
    end_time         TEXT NOT NULL,
    location         TEXT NOT NULL DEFAULT 'Straubing',
    location_details TEXT,
    street           TEXT,
    postal_code      TEXT,
    city             TEXT,
    latitude         REAL,
    longitude        REAL,
    max_participants INTEGER NOT NULL DEFAULT 8,
    cost_basis       TEXT NOT NULL DEFAULT 'Auf Spendenbasis',
    image            TEXT,
    is_published     INTEGER NOT NULL DEFAULT 0,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at       DATETIME
);
CREATE INDEX IF NOT EXISTS events_published_date_index ON events (is_published, event_date);

-- Registrations link a participant to an event.
CREATE TABLE IF NOT EXISTS registrations (
    id                   INTEGER PRIMARY KEY AUTOINCREMENT,
    participant_id       INTEGER NOT NULL REFERENCES participants (id) ON DELETE CASCADE,
    event_id             INTEGER NOT NULL REFERENCES events (id) ON DELETE CASCADE,
    status               TEXT NOT NULL DEFAULT 'registered',
    registered_at        DATETIME,
    cancelled_at         DATETIME,
    reminder_sent_at     DATETIME,
    sms_reminder_sent_at DATETIME,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at           DATETIME,
    UNIQUE (participant_id, event_id)
);
CREATE INDEX IF NOT EXISTS registrations_event_status_index ON registrations (event_id, status);

-- Newsletter broadcasts.
CREATE TABLE IF NOT EXISTS newsletters (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    subject         TEXT NOT NULL,
    content         TEXT NOT NULL,
    status          TEXT NOT NULL DEFAULT 'draft',
    sent_at         DATETIME,
    recipient_count INTEGER NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Newsletter opt-ins (one per participant).
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    participant_id  INTEGER NOT NULL UNIQUE REFERENCES participants (id) ON DELETE CASCADE,
    token           TEXT NOT NULL UNIQUE,
    subscribed_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_at    DATETIME,
    unsubscribed_at DATETIME,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at      DATETIME
);

-- Moderated testimonials.
CREATE TABLE IF NOT EXISTS testimonials (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    quote        TEXT NOT NULL,
    author_name  TEXT,
    email        TEXT,
    role         TEXT,
    is_published INTEGER NOT NULL DEFAULT 0,
    published_at DATETIME,
    sort_order   INTEGER NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at   DATETIME
);

-- CMS pages composed of content blocks.
CREATE TABLE IF NOT EXISTS pages (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT NOT NULL,
    slug         TEXT NOT NULL UNIQUE,
    meta         TEXT,
    is_published INTEGER NOT NULL DEFAULT 0,
    published_at DATETIME,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at   DATETIME
);

CREATE TABLE IF NOT EXISTS content_blocks (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id    INTEGER REFERENCES pages (id) ON DELETE CASCADE,
    type       TEXT NOT NULL,
    data       TEXT NOT NULL DEFAULT '{}',
    block_id   TEXT NOT NULL UNIQUE,
    "order"    INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS content_blocks_page_order_index ON content_blocks (page_id, "order");

-- Header/footer navigation items.
CREATE TABLE IF NOT EXISTS navigation_items (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    location           TEXT NOT NULL,
    label              TEXT NOT NULL,
    url                TEXT NOT NULL DEFAULT '',
    anchor             TEXT,
    condition          TEXT,
    open_in_new_tab    INTEGER NOT NULL DEFAULT 0,
    is_cta             INTEGER NOT NULL DEFAULT 0,
    is_visible         INTEGER NOT NULL DEFAULT 1,
    umami_event_target TEXT,
    sort               INTEGER NOT NULL DEFAULT 0,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS navigation_items_location_index ON navigation_items (location);

-- Key/value settings (mirrors Spatie settings "group"/"name"/"payload").
CREATE TABLE IF NOT EXISTS settings (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    "group"    TEXT NOT NULL,
    name       TEXT NOT NULL,
    payload    TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE ("group", name)
);

-- Admin panel sessions.
CREATE TABLE IF NOT EXISTS sessions (
    id            TEXT PRIMARY KEY,
    user_id       INTEGER REFERENCES users (id) ON DELETE CASCADE,
    data          TEXT NOT NULL DEFAULT '{}',
    expires_at    DATETIME NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS sessions_expires_index ON sessions (expires_at);
