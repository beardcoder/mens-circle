package models

import "time"

// Newsletter is a broadcast email that can be drafted and sent to subscribers.
type Newsletter struct {
	ID             int64            `json:"id"`
	Subject        string           `json:"subject"`
	Content        string           `json:"content"`
	Status         NewsletterStatus `json:"status"`
	SentAt         *time.Time       `json:"sent_at"`
	RecipientCount int              `json:"recipient_count"`
	CreatedAt      time.Time        `json:"created_at"`
	UpdatedAt      time.Time        `json:"updated_at"`
}

// NewsletterSubscription is a participant's opt-in to the newsletter.
type NewsletterSubscription struct {
	ID             int64      `json:"id"`
	ParticipantID  int64      `json:"participant_id"`
	Token          string     `json:"token"`
	SubscribedAt   time.Time  `json:"subscribed_at"`
	ConfirmedAt    *time.Time `json:"confirmed_at"`
	UnsubscribedAt *time.Time `json:"unsubscribed_at"`
	CreatedAt      time.Time  `json:"created_at"`
	UpdatedAt      time.Time  `json:"updated_at"`
	DeletedAt      *time.Time `json:"deleted_at"`

	Participant *Participant `json:"participant,omitempty"`
}

// IsActive reports whether the subscription is currently active.
func (s NewsletterSubscription) IsActive() bool {
	return s.UnsubscribedAt == nil
}
