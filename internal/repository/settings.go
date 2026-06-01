package repository

import (
	"encoding/json"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type SettingsRepository struct{ db *database.DB }

const settingsGroup = "general"

// payloads maps each GeneralSettings field to its stored "name" and a pointer
// to marshal/unmarshal against. This mirrors Spatie's per-property storage.
func settingsPayloads(s *models.GeneralSettings) map[string]any {
	return map[string]any{
		"site_name":                      &s.SiteName,
		"site_tagline":                   &s.SiteTagline,
		"site_description":               &s.SiteDescription,
		"contact_email":                  &s.ContactEmail,
		"contact_phone":                  &s.ContactPhone,
		"location":                       &s.Location,
		"whatsapp_community_link":        &s.WhatsAppCommunityLink,
		"social_links":                   &s.SocialLinks,
		"footer_text":                    &s.FooterText,
		"event_default_max_participants": &s.EventDefaultMaxParticipants,
	}
}

// Load returns the persisted general settings, falling back to defaults for any
// missing rows.
func (r *SettingsRepository) Load() (*models.GeneralSettings, error) {
	settings := models.DefaultSettings()

	rows, err := r.db.Query(`SELECT name, payload FROM settings WHERE "group" = ?`, settingsGroup)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	stored := map[string]string{}
	for rows.Next() {
		var name, payload string
		if err := rows.Scan(&name, &payload); err != nil {
			return nil, err
		}
		stored[name] = payload
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}

	targets := settingsPayloads(&settings)
	for name, target := range targets {
		if payload, ok := stored[name]; ok && payload != "" {
			_ = json.Unmarshal([]byte(payload), target)
		}
	}
	return &settings, nil
}

// Save persists every general settings field as an upserted row.
func (r *SettingsRepository) Save(s *models.GeneralSettings) error {
	tx, err := r.db.Begin()
	if err != nil {
		return err
	}
	defer tx.Rollback() //nolint:errcheck

	for name, value := range settingsPayloads(s) {
		payload, err := json.Marshal(value)
		if err != nil {
			return err
		}
		if _, err := tx.Exec(`INSERT INTO settings ("group", name, payload) VALUES (?, ?, ?)
			ON CONFLICT ("group", name) DO UPDATE SET payload = excluded.payload, updated_at = CURRENT_TIMESTAMP`,
			settingsGroup, name, string(payload)); err != nil {
			return err
		}
	}
	return tx.Commit()
}
