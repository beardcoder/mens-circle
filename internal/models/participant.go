package models

import (
	"strings"
	"time"
)

// Participant is a person who registers for events and/or subscribes to the
// newsletter. A participant is uniquely identified by their email address and
// is the central entity that registrations and subscriptions hang off of.
type Participant struct {
	ID        int64     `json:"id"`
	FirstName *string   `json:"first_name"`
	LastName  *string   `json:"last_name"`
	Email     string    `json:"email"`
	Phone     *string   `json:"phone"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// FullName returns the trimmed concatenation of first and last name.
func (p Participant) FullName() string {
	var b strings.Builder
	if p.FirstName != nil {
		b.WriteString(strings.TrimSpace(*p.FirstName))
	}
	if p.LastName != nil {
		if b.Len() > 0 {
			b.WriteByte(' ')
		}
		b.WriteString(strings.TrimSpace(*p.LastName))
	}
	return strings.TrimSpace(b.String())
}

// DisplayName returns the full name, falling back to the email address.
func (p Participant) DisplayName() string {
	if name := p.FullName(); name != "" {
		return name
	}
	return p.Email
}
