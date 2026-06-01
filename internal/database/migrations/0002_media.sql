-- Uploaded media files (images for content blocks and the media library).
CREATE TABLE IF NOT EXISTS media (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    collection  TEXT NOT NULL DEFAULT 'uploads',
    name        TEXT NOT NULL,
    file_name   TEXT NOT NULL UNIQUE,
    mime_type   TEXT,
    size        INTEGER NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS media_collection_index ON media (collection);
