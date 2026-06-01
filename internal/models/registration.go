package models

import "time"

// Registration links a Participant to an Event with a lifecycle status.
type Registration struct {
	ID                int64              `json:"id"`
	ParticipantID     int64              `json:"participant_id"`
	EventID           int64              `json:"event_id"`
	Status            RegistrationStatus `json:"status"`
	RegisteredAt      *time.Time         `json:"registered_at"`
	CancelledAt       *time.Time         `json:"cancelled_at"`
	ReminderSentAt    *time.Time         `json:"reminder_sent_at"`
	SMSReminderSentAt *time.Time         `json:"sms_reminder_sent_at"`
	CreatedAt         time.Time          `json:"created_at"`
	UpdatedAt         time.Time          `json:"updated_at"`
	DeletedAt         *time.Time         `json:"deleted_at"`

	// Eager-loaded relations (optional, populated by the repository).
	Participant *Participant `json:"participant,omitempty"`
	Event       *Event       `json:"event,omitempty"`
}

// IsActive reports whether the registration currently occupies a seat.
func (r Registration) IsActive() bool {
	return r.Status.IsActive()
}
