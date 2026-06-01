package models

import "time"

// Media is an uploaded file (typically an image) stored on disk and served
// under /media/{FileName}.
type Media struct {
	ID         int64     `json:"id"`
	Collection string    `json:"collection"`
	Name       string    `json:"name"`
	FileName   string    `json:"file_name"`
	MimeType   *string   `json:"mime_type"`
	Size       int64     `json:"size"`
	CreatedAt  time.Time `json:"created_at"`
}

// URL returns the public path the file is served from.
func (m Media) URL() string {
	return "/media/" + m.FileName
}

// IsImage reports whether the file is an image based on its MIME type.
func (m Media) IsImage() bool {
	return m.MimeType != nil && len(*m.MimeType) >= 6 && (*m.MimeType)[:6] == "image/"
}
