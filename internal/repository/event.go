package repository

import (
	"database/sql"
	"errors"
	"time"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type EventRepository struct{ db *database.DB }

// activeRegSubquery counts seat-occupying registrations for an event.
const activeRegSubquery = `(SELECT COUNT(*) FROM registrations r
	WHERE r.event_id = e.id AND r.deleted_at IS NULL
	AND r.status IN ('registered', 'attended'))`

const eventColumns = `e.id, e.title, e.slug, e.description, e.event_date, e.start_time, e.end_time,
	e.location, e.location_details, e.street, e.postal_code, e.city, e.latitude, e.longitude,
	e.max_participants, e.cost_basis, e.image, e.is_published, e.created_at, e.updated_at, e.deleted_at, ` + activeRegSubquery

func scanEvent(row interface{ Scan(...any) error }) (*models.Event, error) {
	var e models.Event
	var description, locationDetails, street, postal, city, image sql.NullString
	var lat, lng sql.NullFloat64
	var deletedAt sql.NullTime
	if err := row.Scan(
		&e.ID, &e.Title, &e.Slug, &description, &e.EventDate, &e.StartTime, &e.EndTime,
		&e.Location, &locationDetails, &street, &postal, &city, &lat, &lng,
		&e.MaxParticipants, &e.CostBasis, &image, &e.IsPublished, &e.CreatedAt, &e.UpdatedAt, &deletedAt,
		&e.ActiveRegistrations,
	); err != nil {
		return nil, err
	}
	e.Description = ptrString(description)
	e.LocationDetails = ptrString(locationDetails)
	e.Street = ptrString(street)
	e.PostalCode = ptrString(postal)
	e.City = ptrString(city)
	e.Latitude = ptrFloat(lat)
	e.Longitude = ptrFloat(lng)
	e.Image = ptrString(image)
	e.DeletedAt = ptrTime(deletedAt)
	return &e, nil
}

// FindBySlug returns a published, non-deleted event by slug.
func (r *EventRepository) FindBySlug(slug string) (*models.Event, error) {
	row := r.db.QueryRow(`SELECT `+eventColumns+` FROM events e WHERE e.slug = ? AND e.deleted_at IS NULL`, slug)
	e, err := scanEvent(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return e, err
}

// FindByID returns any non-deleted event by id.
func (r *EventRepository) FindByID(id int64) (*models.Event, error) {
	row := r.db.QueryRow(`SELECT `+eventColumns+` FROM events e WHERE e.id = ? AND e.deleted_at IS NULL`, id)
	e, err := scanEvent(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return e, err
}

// NextUpcoming returns the next published event whose date is today or later.
func (r *EventRepository) NextUpcoming() (*models.Event, error) {
	row := r.db.QueryRow(`SELECT `+eventColumns+` FROM events e
		WHERE e.is_published = 1 AND e.deleted_at IS NULL AND e.event_date >= ?
		ORDER BY e.event_date ASC LIMIT 1`, startOfToday())
	e, err := scanEvent(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return e, err
}

func (r *EventRepository) queryList(where, order string, args ...any) ([]models.Event, error) {
	rows, err := r.db.Query(`SELECT `+eventColumns+` FROM events e WHERE `+where+` `+order, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var out []models.Event
	for rows.Next() {
		e, err := scanEvent(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *e)
	}
	return out, rows.Err()
}

// All returns every non-deleted event for the admin panel (newest first).
func (r *EventRepository) All() ([]models.Event, error) {
	return r.queryList("e.deleted_at IS NULL", "ORDER BY e.event_date DESC")
}

// PublishedUpcoming returns published events from today onwards.
func (r *EventRepository) PublishedUpcoming() ([]models.Event, error) {
	return r.queryList("e.is_published = 1 AND e.deleted_at IS NULL AND e.event_date >= ?",
		"ORDER BY e.event_date ASC", startOfToday())
}

// PublishedPast returns published events whose date is in the past.
func (r *EventRepository) PublishedPast(limit int) ([]models.Event, error) {
	return r.queryList("e.is_published = 1 AND e.deleted_at IS NULL AND e.event_date < ?",
		"ORDER BY e.event_date DESC LIMIT ?", startOfToday(), limit)
}

// UpcomingCount returns the number of published upcoming events.
func (r *EventRepository) UpcomingCount() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM events WHERE is_published = 1 AND deleted_at IS NULL AND event_date >= ?`, startOfToday()).Scan(&n)
	return n, err
}

// Count returns the total number of non-deleted events.
func (r *EventRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM events WHERE deleted_at IS NULL`).Scan(&n)
	return n, err
}

func (r *EventRepository) Create(e *models.Event) error {
	res, err := r.db.Exec(`INSERT INTO events
		(title, slug, description, event_date, start_time, end_time, location, location_details,
		 street, postal_code, city, latitude, longitude, max_participants, cost_basis, image, is_published)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
		e.Title, e.Slug, nullString(e.Description), e.EventDate, e.StartTime, e.EndTime, e.Location,
		nullString(e.LocationDetails), nullString(e.Street), nullString(e.PostalCode), nullString(e.City),
		nullFloat(e.Latitude), nullFloat(e.Longitude), e.MaxParticipants, e.CostBasis, nullString(e.Image), e.IsPublished,
	)
	if err != nil {
		return err
	}
	e.ID, _ = res.LastInsertId()
	return nil
}

func (r *EventRepository) Update(e *models.Event) error {
	_, err := r.db.Exec(`UPDATE events SET
		title = ?, slug = ?, description = ?, event_date = ?, start_time = ?, end_time = ?, location = ?,
		location_details = ?, street = ?, postal_code = ?, city = ?, latitude = ?, longitude = ?,
		max_participants = ?, cost_basis = ?, image = ?, is_published = ?, updated_at = CURRENT_TIMESTAMP
		WHERE id = ?`,
		e.Title, e.Slug, nullString(e.Description), e.EventDate, e.StartTime, e.EndTime, e.Location,
		nullString(e.LocationDetails), nullString(e.Street), nullString(e.PostalCode), nullString(e.City),
		nullFloat(e.Latitude), nullFloat(e.Longitude), e.MaxParticipants, e.CostBasis, nullString(e.Image), e.IsPublished,
		e.ID,
	)
	return err
}

// SoftDelete marks an event as deleted without removing the row.
func (r *EventRepository) SoftDelete(id int64) error {
	_, err := r.db.Exec(`UPDATE events SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?`, id)
	return err
}

func startOfToday() time.Time {
	now := time.Now()
	return time.Date(now.Year(), now.Month(), now.Day(), 0, 0, 0, 0, now.Location())
}
