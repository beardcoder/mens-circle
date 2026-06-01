package models

import (
	"fmt"
	"strings"
	"time"
)

// Event is a men's circle gathering that participants can register for.
type Event struct {
	ID              int64      `json:"id"`
	Title           string     `json:"title"`
	Slug            string     `json:"slug"`
	Description     *string    `json:"description"`
	EventDate       time.Time  `json:"event_date"`
	StartTime       string     `json:"start_time"` // "HH:MM"
	EndTime         string     `json:"end_time"`   // "HH:MM"
	Location        string     `json:"location"`
	LocationDetails *string    `json:"location_details"`
	Street          *string    `json:"street"`
	PostalCode      *string    `json:"postal_code"`
	City            *string    `json:"city"`
	Latitude        *float64   `json:"latitude"`
	Longitude       *float64   `json:"longitude"`
	MaxParticipants int        `json:"max_participants"`
	CostBasis       string     `json:"cost_basis"`
	Image           *string    `json:"image"`
	IsPublished     bool       `json:"is_published"`
	CreatedAt       time.Time  `json:"created_at"`
	UpdatedAt       time.Time  `json:"updated_at"`
	DeletedAt       *time.Time `json:"deleted_at"`

	// ActiveRegistrations is populated by the repository (count of registered
	// or attended registrations). It is not a database column.
	ActiveRegistrations int `json:"active_registrations"`
}

// AvailableSpots returns how many seats are still free.
func (e Event) AvailableSpots() int {
	spots := e.MaxParticipants - e.ActiveRegistrations
	if spots < 0 {
		return 0
	}
	return spots
}

// IsFull reports whether the event has no remaining seats.
func (e Event) IsFull() bool {
	return e.AvailableSpots() <= 0
}

// IsPast reports whether the event date lies before today.
func (e Event) IsPast() bool {
	endOfDay := time.Date(e.EventDate.Year(), e.EventDate.Month(), e.EventDate.Day(), 23, 59, 59, 0, e.EventDate.Location())
	return endOfDay.Before(time.Now())
}

// FullAddress returns the formatted street address, or empty if incomplete.
func (e Event) FullAddress() string {
	if e.Street == nil || e.City == nil {
		return ""
	}
	postal := ""
	if e.PostalCode != nil {
		postal = *e.PostalCode
	}
	return strings.TrimSpace(fmt.Sprintf("%s, %s %s", *e.Street, postal, *e.City))
}

// HasCoordinates reports whether both latitude and longitude are set.
func (e Event) HasCoordinates() bool {
	return e.Latitude != nil && e.Longitude != nil
}

// GenerateICalContent renders an RFC 5545 VEVENT for calendar integration.
func (e Event) GenerateICalContent(baseURL string) string {
	stamp := time.Now().UTC().Format("20060102T150405Z")
	start := e.combine(e.StartTime).Format("20060102T150405")
	end := e.combine(e.EndTime).Format("20060102T150405")
	desc := ""
	if e.Description != nil {
		desc = strings.ReplaceAll(*e.Description, "\n", "\\n")
	}
	location := e.Location
	if addr := e.FullAddress(); addr != "" {
		location = addr
	}
	var b strings.Builder
	b.WriteString("BEGIN:VCALENDAR\r\n")
	b.WriteString("VERSION:2.0\r\n")
	b.WriteString("PRODID:-//Maennerkreis Niederbayern//Events//DE\r\n")
	b.WriteString("CALSCALE:GREGORIAN\r\n")
	b.WriteString("BEGIN:VEVENT\r\n")
	fmt.Fprintf(&b, "UID:event-%d@maennerkreis-niederbayern.de\r\n", e.ID)
	fmt.Fprintf(&b, "DTSTAMP:%s\r\n", stamp)
	fmt.Fprintf(&b, "DTSTART:%s\r\n", start)
	fmt.Fprintf(&b, "DTEND:%s\r\n", end)
	fmt.Fprintf(&b, "SUMMARY:%s\r\n", e.Title)
	fmt.Fprintf(&b, "DESCRIPTION:%s\r\n", desc)
	fmt.Fprintf(&b, "LOCATION:%s\r\n", location)
	fmt.Fprintf(&b, "URL:%s/event/%s\r\n", strings.TrimRight(baseURL, "/"), e.Slug)
	b.WriteString("END:VEVENT\r\n")
	b.WriteString("END:VCALENDAR\r\n")
	return b.String()
}

// combine merges the event date with an "HH:MM" time string.
func (e Event) combine(hhmm string) time.Time {
	h, m := 0, 0
	fmt.Sscanf(hhmm, "%d:%d", &h, &m)
	return time.Date(e.EventDate.Year(), e.EventDate.Month(), e.EventDate.Day(), h, m, 0, 0, e.EventDate.Location())
}
