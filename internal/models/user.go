package models

import "time"

// User is an administrator who can access the admin panel.
type User struct {
	ID              int64      `json:"id"`
	Name            string     `json:"name"`
	Email           string     `json:"email"`
	EmailVerifiedAt *time.Time `json:"email_verified_at"`
	PasswordHash    string     `json:"-"`
	GitHubID        *string    `json:"github_id"`
	CreatedAt       time.Time  `json:"created_at"`
	UpdatedAt       time.Time  `json:"updated_at"`
}
