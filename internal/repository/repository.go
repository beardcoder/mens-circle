// Package repository provides data access for all domain models.
package repository

import (
	"database/sql"
	"time"

	"github.com/beardcoder/mens-circle/internal/database"
)

// Repositories bundles all entity repositories behind a single struct so
// handlers can depend on one value.
type Repositories struct {
	db *database.DB

	Users         *UserRepository
	Participants  *ParticipantRepository
	Events        *EventRepository
	Registrations *RegistrationRepository
	Newsletters   *NewsletterRepository
	Subscriptions *SubscriptionRepository
	Testimonials  *TestimonialRepository
	Pages         *PageRepository
	Navigation    *NavigationRepository
	Settings      *SettingsRepository
}

// New wires up every repository against the shared database connection.
func New(db *database.DB) *Repositories {
	return &Repositories{
		db:            db,
		Users:         &UserRepository{db},
		Participants:  &ParticipantRepository{db},
		Events:        &EventRepository{db},
		Registrations: &RegistrationRepository{db},
		Newsletters:   &NewsletterRepository{db},
		Subscriptions: &SubscriptionRepository{db},
		Testimonials:  &TestimonialRepository{db},
		Pages:         &PageRepository{db},
		Navigation:    &NavigationRepository{db},
		Settings:      &SettingsRepository{db},
	}
}

// --- shared scan helpers for nullable columns ---

func nullString(s *string) any {
	if s == nil {
		return nil
	}
	return *s
}

func nullTime(t *time.Time) any {
	if t == nil {
		return nil
	}
	return *t
}

func nullFloat(f *float64) any {
	if f == nil {
		return nil
	}
	return *f
}

func ptrString(n sql.NullString) *string {
	if !n.Valid {
		return nil
	}
	v := n.String
	return &v
}

func ptrTime(n sql.NullTime) *time.Time {
	if !n.Valid {
		return nil
	}
	v := n.Time
	return &v
}

func ptrFloat(n sql.NullFloat64) *float64 {
	if !n.Valid {
		return nil
	}
	v := n.Float64
	return &v
}
