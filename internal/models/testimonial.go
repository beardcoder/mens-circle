package models

import "time"

// Testimonial is a moderated quote submitted by a participant.
type Testimonial struct {
	ID          int64      `json:"id"`
	Quote       string     `json:"quote"`
	AuthorName  *string    `json:"author_name"`
	Email       *string    `json:"email"`
	Role        *string    `json:"role"`
	IsPublished bool       `json:"is_published"`
	PublishedAt *time.Time `json:"published_at"`
	SortOrder   int        `json:"sort_order"`
	CreatedAt   time.Time  `json:"created_at"`
	UpdatedAt   time.Time  `json:"updated_at"`
	DeletedAt   *time.Time `json:"deleted_at"`
}
