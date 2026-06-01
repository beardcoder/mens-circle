package repository

import (
	"database/sql"
	"errors"
	"time"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type RegistrationRepository struct{ db *database.DB }

const registrationColumns = `id, participant_id, event_id, status, registered_at, cancelled_at,
	reminder_sent_at, sms_reminder_sent_at, created_at, updated_at, deleted_at`

func scanRegistration(row interface{ Scan(...any) error }) (*models.Registration, error) {
	var r models.Registration
	var status string
	var registeredAt, cancelledAt, reminderAt, smsReminderAt, deletedAt sql.NullTime
	if err := row.Scan(&r.ID, &r.ParticipantID, &r.EventID, &status, &registeredAt, &cancelledAt,
		&reminderAt, &smsReminderAt, &r.CreatedAt, &r.UpdatedAt, &deletedAt); err != nil {
		return nil, err
	}
	r.Status = models.RegistrationStatus(status)
	r.RegisteredAt = ptrTime(registeredAt)
	r.CancelledAt = ptrTime(cancelledAt)
	r.ReminderSentAt = ptrTime(reminderAt)
	r.SMSReminderSentAt = ptrTime(smsReminderAt)
	r.DeletedAt = ptrTime(deletedAt)
	return &r, nil
}

// FindActiveByParticipantAndEvent returns a non-deleted registration linking a
// participant and event, or ErrNotFound.
func (r *RegistrationRepository) FindByParticipantAndEvent(participantID, eventID int64) (*models.Registration, error) {
	row := r.db.QueryRow(`SELECT `+registrationColumns+` FROM registrations
		WHERE participant_id = ? AND event_id = ? AND deleted_at IS NULL`, participantID, eventID)
	reg, err := scanRegistration(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return reg, err
}

func (r *RegistrationRepository) Create(reg *models.Registration) error {
	now := time.Now()
	if reg.RegisteredAt == nil {
		reg.RegisteredAt = &now
	}
	res, err := r.db.Exec(`INSERT INTO registrations (participant_id, event_id, status, registered_at)
		VALUES (?, ?, ?, ?)`, reg.ParticipantID, reg.EventID, string(reg.Status), nullTime(reg.RegisteredAt))
	if err != nil {
		return err
	}
	reg.ID, _ = res.LastInsertId()
	return nil
}

// TransitionTo updates the status, recording cancelled_at when cancelling.
func (r *RegistrationRepository) TransitionTo(id int64, status models.RegistrationStatus) error {
	var cancelledAt any
	if status == models.RegistrationCancelled {
		cancelledAt = time.Now()
	}
	_, err := r.db.Exec(`UPDATE registrations SET status = ?, cancelled_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		string(status), cancelledAt, id)
	return err
}

// PromoteNextWaitlisted promotes the oldest waitlisted registration for an
// event to "registered" and returns it, or ErrNotFound if the waitlist empty.
func (r *RegistrationRepository) PromoteNextWaitlisted(eventID int64) (*models.Registration, error) {
	row := r.db.QueryRow(`SELECT `+registrationColumns+` FROM registrations
		WHERE event_id = ? AND status = 'waitlist' AND deleted_at IS NULL
		ORDER BY registered_at ASC LIMIT 1`, eventID)
	reg, err := scanRegistration(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	if err != nil {
		return nil, err
	}
	if err := r.TransitionTo(reg.ID, models.RegistrationRegistered); err != nil {
		return nil, err
	}
	reg.Status = models.RegistrationRegistered
	return reg, nil
}

// ForEvent returns all non-deleted registrations for an event with their
// participant eager-loaded.
func (r *RegistrationRepository) ForEvent(eventID int64) ([]models.Registration, error) {
	rows, err := r.db.Query(`SELECT `+registrationColumns+` FROM registrations
		WHERE event_id = ? AND deleted_at IS NULL ORDER BY registered_at ASC`, eventID)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var out []models.Registration
	for rows.Next() {
		reg, err := scanRegistration(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *reg)
	}
	return out, rows.Err()
}

// ListWithRelations returns registrations joined with participant + event data
// for the admin panel.
func (r *RegistrationRepository) ListWithRelations(limit int) ([]models.Registration, error) {
	rows, err := r.db.Query(`SELECT
		reg.id, reg.participant_id, reg.event_id, reg.status, reg.registered_at, reg.cancelled_at,
		reg.reminder_sent_at, reg.sms_reminder_sent_at, reg.created_at, reg.updated_at, reg.deleted_at,
		p.first_name, p.last_name, p.email, e.title, e.slug, e.event_date
		FROM registrations reg
		JOIN participants p ON p.id = reg.participant_id
		JOIN events e ON e.id = reg.event_id
		WHERE reg.deleted_at IS NULL
		ORDER BY reg.created_at DESC LIMIT ?`, limit)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var out []models.Registration
	for rows.Next() {
		var reg models.Registration
		var status string
		var registeredAt, cancelledAt, reminderAt, smsReminderAt, deletedAt sql.NullTime
		var first, last sql.NullString
		p := &models.Participant{}
		e := &models.Event{}
		if err := rows.Scan(
			&reg.ID, &reg.ParticipantID, &reg.EventID, &status, &registeredAt, &cancelledAt,
			&reminderAt, &smsReminderAt, &reg.CreatedAt, &reg.UpdatedAt, &deletedAt,
			&first, &last, &p.Email, &e.Title, &e.Slug, &e.EventDate,
		); err != nil {
			return nil, err
		}
		reg.Status = models.RegistrationStatus(status)
		reg.RegisteredAt = ptrTime(registeredAt)
		reg.CancelledAt = ptrTime(cancelledAt)
		reg.ReminderSentAt = ptrTime(reminderAt)
		reg.SMSReminderSentAt = ptrTime(smsReminderAt)
		reg.DeletedAt = ptrTime(deletedAt)
		p.ID = reg.ParticipantID
		p.FirstName = ptrString(first)
		p.LastName = ptrString(last)
		e.ID = reg.EventID
		reg.Participant = p
		reg.Event = e
		out = append(out, reg)
	}
	return out, rows.Err()
}

// Count returns the total number of non-deleted registrations.
func (r *RegistrationRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM registrations WHERE deleted_at IS NULL`).Scan(&n)
	return n, err
}

// DueReminders returns active registrations for published events happening
// between now and the given cutoff that have not had a reminder sent.
func (r *RegistrationRepository) DueReminders(from, to time.Time) ([]models.Registration, error) {
	rows, err := r.db.Query(`SELECT `+regWithRelCols+` FROM registrations reg
		JOIN participants p ON p.id = reg.participant_id
		JOIN events e ON e.id = reg.event_id
		WHERE reg.deleted_at IS NULL AND reg.status IN ('registered','attended')
		AND reg.reminder_sent_at IS NULL AND e.is_published = 1
		AND e.event_date BETWEEN ? AND ?`, from, to)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	return scanRegWithRel(rows)
}

// MarkReminderSent records that a reminder email (and optionally SMS) was sent.
func (r *RegistrationRepository) MarkReminderSent(id int64, sms bool) error {
	if sms {
		_, err := r.db.Exec(`UPDATE registrations SET reminder_sent_at = CURRENT_TIMESTAMP, sms_reminder_sent_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?`, id)
		return err
	}
	_, err := r.db.Exec(`UPDATE registrations SET reminder_sent_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = ?`, id)
	return err
}

const regWithRelCols = `reg.id, reg.participant_id, reg.event_id, reg.status, reg.registered_at, reg.cancelled_at,
	reg.reminder_sent_at, reg.sms_reminder_sent_at, reg.created_at, reg.updated_at, reg.deleted_at,
	p.first_name, p.last_name, p.email, p.phone, e.title, e.slug, e.event_date, e.start_time, e.location`

func scanRegWithRel(rows *sql.Rows) ([]models.Registration, error) {
	var out []models.Registration
	for rows.Next() {
		var reg models.Registration
		var status string
		var registeredAt, cancelledAt, reminderAt, smsReminderAt, deletedAt sql.NullTime
		var first, last, phone sql.NullString
		p := &models.Participant{}
		e := &models.Event{}
		if err := rows.Scan(
			&reg.ID, &reg.ParticipantID, &reg.EventID, &status, &registeredAt, &cancelledAt,
			&reminderAt, &smsReminderAt, &reg.CreatedAt, &reg.UpdatedAt, &deletedAt,
			&first, &last, &p.Email, &phone, &e.Title, &e.Slug, &e.EventDate, &e.StartTime, &e.Location,
		); err != nil {
			return nil, err
		}
		reg.Status = models.RegistrationStatus(status)
		reg.RegisteredAt = ptrTime(registeredAt)
		reg.CancelledAt = ptrTime(cancelledAt)
		reg.ReminderSentAt = ptrTime(reminderAt)
		reg.SMSReminderSentAt = ptrTime(smsReminderAt)
		reg.DeletedAt = ptrTime(deletedAt)
		p.ID = reg.ParticipantID
		p.FirstName = ptrString(first)
		p.LastName = ptrString(last)
		p.Phone = ptrString(phone)
		e.ID = reg.EventID
		reg.Participant = p
		reg.Event = e
		out = append(out, reg)
	}
	return out, rows.Err()
}
