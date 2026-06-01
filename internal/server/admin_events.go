package server

import (
	"fmt"
	"net/http"
	"strconv"
	"strings"
	"time"

	"github.com/beardcoder/mens-circle/internal/models"
)

func (s *Server) adminEventsList(w http.ResponseWriter, r *http.Request) {
	events, err := s.repos.Events.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Veranstaltungen", "events")
	view.Data = events
	s.renderAdmin(w, r, "events_list", view)
}

func (s *Server) adminEventForm(w http.ResponseWriter, r *http.Request) {
	view := s.adminBase(r, "Veranstaltung", "events")
	if idStr := r.PathValue("id"); idStr != "" {
		id, _ := strconv.ParseInt(idStr, 10, 64)
		event, err := s.repos.Events.FindByID(id)
		if err != nil {
			s.notFound(w, r)
			return
		}
		view.Data = event
	} else {
		settings := s.settings()
		view.Data = &models.Event{
			EventDate:       time.Now().AddDate(0, 0, 7),
			StartTime:       "19:00",
			EndTime:         "21:00",
			Location:        settings.Location,
			CostBasis:       "Auf Spendenbasis",
			MaxParticipants: settings.EventDefaultMaxParticipants,
		}
	}
	s.renderAdmin(w, r, "event_form", view)
}

// eventFromForm reads event fields from the request form.
func (s *Server) eventFromForm(r *http.Request, e *models.Event) error {
	e.Title = strings.TrimSpace(r.FormValue("title"))
	e.Slug = strings.TrimSpace(r.FormValue("slug"))
	if e.Slug == "" {
		e.Slug = slugify(e.Title)
	}
	e.Location = strings.TrimSpace(r.FormValue("location"))
	e.CostBasis = strings.TrimSpace(r.FormValue("cost_basis"))
	e.StartTime = r.FormValue("start_time")
	e.EndTime = r.FormValue("end_time")
	e.IsPublished = r.FormValue("is_published") != ""

	if v := strings.TrimSpace(r.FormValue("description")); v != "" {
		e.Description = &v
	} else {
		e.Description = nil
	}
	e.Street = optionalString(r.FormValue("street"))
	e.PostalCode = optionalString(r.FormValue("postal_code"))
	e.City = optionalString(r.FormValue("city"))
	e.LocationDetails = optionalString(r.FormValue("location_details"))

	date, err := time.Parse("2006-01-02", r.FormValue("event_date"))
	if err != nil {
		return fmt.Errorf("ungültiges Datum")
	}
	e.EventDate = date

	if v := r.FormValue("max_participants"); v != "" {
		if n, err := strconv.Atoi(v); err == nil {
			e.MaxParticipants = n
		}
	}
	if v := r.FormValue("latitude"); v != "" {
		if f, err := strconv.ParseFloat(v, 64); err == nil {
			e.Latitude = &f
		}
	}
	if v := r.FormValue("longitude"); v != "" {
		if f, err := strconv.ParseFloat(v, 64); err == nil {
			e.Longitude = &f
		}
	}
	return nil
}

func (s *Server) adminEventCreate(w http.ResponseWriter, r *http.Request) {
	var e models.Event
	if err := s.eventFromForm(r, &e); err != nil {
		s.flashRedirect(w, r, "error", err.Error(), "/admin/events/new")
		return
	}
	if err := s.repos.Events.Create(&e); err != nil {
		s.flashRedirect(w, r, "error", "Speichern fehlgeschlagen: "+err.Error(), "/admin/events/new")
		return
	}
	s.flashRedirect(w, r, "success", "Veranstaltung wurde erstellt.", "/admin/events")
}

func (s *Server) adminEventUpdate(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	event, err := s.repos.Events.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	if err := s.eventFromForm(r, event); err != nil {
		s.flashRedirect(w, r, "error", err.Error(), fmt.Sprintf("/admin/events/%d/edit", id))
		return
	}
	if err := s.repos.Events.Update(event); err != nil {
		s.flashRedirect(w, r, "error", "Speichern fehlgeschlagen: "+err.Error(), fmt.Sprintf("/admin/events/%d/edit", id))
		return
	}
	s.flashRedirect(w, r, "success", "Veranstaltung wurde aktualisiert.", "/admin/events")
}

func (s *Server) adminEventDelete(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	if err := s.repos.Events.SoftDelete(id); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Veranstaltung wurde gelöscht.", "/admin/events")
}

func optionalString(v string) *string {
	v = strings.TrimSpace(v)
	if v == "" {
		return nil
	}
	return &v
}

// slugify produces a URL-safe slug from arbitrary text.
func slugify(s string) string {
	s = strings.ToLower(strings.TrimSpace(s))
	replacer := strings.NewReplacer("ä", "ae", "ö", "oe", "ü", "ue", "ß", "ss")
	s = replacer.Replace(s)
	var b strings.Builder
	prevDash := false
	for _, r := range s {
		switch {
		case (r >= 'a' && r <= 'z') || (r >= '0' && r <= '9'):
			b.WriteRune(r)
			prevDash = false
		default:
			if !prevDash && b.Len() > 0 {
				b.WriteByte('-')
				prevDash = true
			}
		}
	}
	return strings.Trim(b.String(), "-")
}
