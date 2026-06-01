package server

import (
	"encoding/json"
	"fmt"
	"html/template"
	"net/http"
	"strconv"
	"strings"
	"time"

	"github.com/beardcoder/mens-circle/internal/blocks"
	"github.com/beardcoder/mens-circle/internal/models"
)

// --- Registrations ---

func (s *Server) adminRegistrationsList(w http.ResponseWriter, r *http.Request) {
	regs, err := s.repos.Registrations.ListWithRelations(500)
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Anmeldungen", "registrations")
	view.Data = struct {
		Registrations []models.Registration
		Statuses      []models.RegistrationStatus
	}{regs, models.RegistrationStatuses()}
	s.renderAdmin(w, r, "registrations_list", view)
}

func (s *Server) adminRegistrationStatus(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	status := models.RegistrationStatus(r.FormValue("status"))
	if err := s.repos.Registrations.TransitionTo(id, status); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Status wurde aktualisiert.", "/admin/registrations")
}

// --- Participants ---

func (s *Server) adminParticipantsList(w http.ResponseWriter, r *http.Request) {
	participants, err := s.repos.Participants.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Teilnehmer", "participants")
	view.Data = participants
	s.renderAdmin(w, r, "participants_list", view)
}

// --- Testimonials ---

func (s *Server) adminTestimonialsList(w http.ResponseWriter, r *http.Request) {
	items, err := s.repos.Testimonials.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Testimonials", "testimonials")
	view.Data = items
	s.renderAdmin(w, r, "testimonials_list", view)
}

func (s *Server) adminTestimonialForm(w http.ResponseWriter, r *http.Request) {
	view := s.adminBase(r, "Testimonial", "testimonials")
	if idStr := r.PathValue("id"); idStr != "" {
		id, _ := strconv.ParseInt(idStr, 10, 64)
		t, err := s.repos.Testimonials.FindByID(id)
		if err != nil {
			s.notFound(w, r)
			return
		}
		view.Data = t
	} else {
		view.Data = &models.Testimonial{}
	}
	s.renderAdmin(w, r, "testimonial_form", view)
}

func (s *Server) testimonialFromForm(r *http.Request, t *models.Testimonial) {
	t.Quote = strings.TrimSpace(r.FormValue("quote"))
	t.AuthorName = optionalString(r.FormValue("author_name"))
	t.Email = optionalString(r.FormValue("email"))
	t.Role = optionalString(r.FormValue("role"))
	t.IsPublished = r.FormValue("is_published") != ""
	if t.IsPublished && t.PublishedAt == nil {
		now := time.Now()
		t.PublishedAt = &now
	}
	if v := r.FormValue("sort_order"); v != "" {
		t.SortOrder, _ = strconv.Atoi(v)
	}
}

func (s *Server) adminTestimonialCreate(w http.ResponseWriter, r *http.Request) {
	var t models.Testimonial
	s.testimonialFromForm(r, &t)
	if err := s.repos.Testimonials.Create(&t); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Testimonial wurde erstellt.", "/admin/testimonials")
}

func (s *Server) adminTestimonialUpdate(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	t, err := s.repos.Testimonials.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	s.testimonialFromForm(r, t)
	if err := s.repos.Testimonials.Update(t); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Testimonial wurde aktualisiert.", "/admin/testimonials")
}

func (s *Server) adminTestimonialPublish(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	publish := r.FormValue("publish") == "1"
	if err := s.repos.Testimonials.SetPublished(id, publish); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Sichtbarkeit wurde geändert.", "/admin/testimonials")
}

func (s *Server) adminTestimonialDelete(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	if err := s.repos.Testimonials.SoftDelete(id); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Testimonial wurde gelöscht.", "/admin/testimonials")
}

// --- Newsletters ---

func (s *Server) adminNewslettersList(w http.ResponseWriter, r *http.Request) {
	items, err := s.repos.Newsletters.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Newsletter", "newsletters")
	view.Data = items
	s.renderAdmin(w, r, "newsletters_list", view)
}

func (s *Server) adminNewsletterForm(w http.ResponseWriter, r *http.Request) {
	view := s.adminBase(r, "Newsletter", "newsletters")
	if idStr := r.PathValue("id"); idStr != "" {
		id, _ := strconv.ParseInt(idStr, 10, 64)
		n, err := s.repos.Newsletters.FindByID(id)
		if err != nil {
			s.notFound(w, r)
			return
		}
		view.Data = n
	} else {
		view.Data = &models.Newsletter{Status: models.NewsletterDraft}
	}
	s.renderAdmin(w, r, "newsletter_form", view)
}

func (s *Server) adminNewsletterCreate(w http.ResponseWriter, r *http.Request) {
	n := &models.Newsletter{
		Subject: strings.TrimSpace(r.FormValue("subject")),
		Content: r.FormValue("content"),
		Status:  models.NewsletterDraft,
	}
	if err := s.repos.Newsletters.Create(n); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Newsletter-Entwurf wurde erstellt.", "/admin/newsletters")
}

func (s *Server) adminNewsletterUpdate(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	n, err := s.repos.Newsletters.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	if n.Status == models.NewsletterSent {
		s.flashRedirect(w, r, "error", "Ein bereits versendeter Newsletter kann nicht mehr bearbeitet werden.", "/admin/newsletters")
		return
	}
	n.Subject = strings.TrimSpace(r.FormValue("subject"))
	n.Content = r.FormValue("content")
	if err := s.repos.Newsletters.Update(n); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Newsletter wurde aktualisiert.", "/admin/newsletters")
}

// adminNewsletterSend marks the newsletter as sent and records the recipient
// count. Actual email delivery is handled by the mail integration; this records
// the send and prevents further edits.
func (s *Server) adminNewsletterSend(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	n, err := s.repos.Newsletters.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	if n.Status == models.NewsletterSent {
		s.flashRedirect(w, r, "error", "Dieser Newsletter wurde bereits versendet.", "/admin/newsletters")
		return
	}
	recipients, _ := s.repos.Subscriptions.ActiveCount()
	if err := s.repos.Newsletters.MarkSent(id, recipients); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", fmt.Sprintf("Newsletter wurde als versendet markiert (%d Empfänger).", recipients), "/admin/newsletters")
}

// --- Subscriptions ---

func (s *Server) adminSubscriptionsList(w http.ResponseWriter, r *http.Request) {
	participants, err := s.repos.Subscriptions.ActiveParticipants()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Newsletter-Abonnenten", "subscriptions")
	view.Data = participants
	s.renderAdmin(w, r, "subscriptions_list", view)
}

// --- Pages ---

func (s *Server) adminPagesList(w http.ResponseWriter, r *http.Request) {
	pages, err := s.repos.Pages.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Seiten", "pages")
	view.Data = pages
	s.renderAdmin(w, r, "pages_list", view)
}

func (s *Server) adminPageForm(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	page, err := s.repos.Pages.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}

	// The block editor is a small client-side app driven by the schema and the
	// current blocks, both passed as JSON.
	schemaJSON, _ := json.Marshal(blocks.Definitions())
	blocksJSON, _ := json.Marshal(blockEditorData(page.ContentBlocks))
	mediaItems, _ := s.repos.Media.All()
	mediaJSON, _ := json.Marshal(mediaURLs(mediaItems))

	view := s.adminBase(r, "Seite bearbeiten", "pages")
	view.Data = struct {
		Page       *models.Page
		SchemaJSON template.JS
		BlocksJSON template.JS
		MediaJSON  template.JS
	}{
		Page:       page,
		SchemaJSON: template.JS(schemaJSON),
		BlocksJSON: template.JS(blocksJSON),
		MediaJSON:  template.JS(mediaJSON),
	}
	s.renderAdmin(w, r, "page_form", view)
}

// blockEditorData shapes content blocks for the editor (id, block_id, type, data).
func blockEditorData(blocksIn []models.ContentBlock) []map[string]any {
	out := make([]map[string]any, 0, len(blocksIn))
	for _, b := range blocksIn {
		data := b.Data
		if data == nil {
			data = map[string]any{}
		}
		out = append(out, map[string]any{
			"id":       b.ID,
			"block_id": b.BlockID,
			"type":     string(b.Type),
			"data":     data,
		})
	}
	return out
}

func mediaURLs(items []models.Media) []map[string]string {
	out := make([]map[string]string, 0, len(items))
	for _, m := range items {
		out = append(out, map[string]string{"url": m.URL(), "name": m.Name})
	}
	return out
}

func (s *Server) adminPageUpdate(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	page, err := s.repos.Pages.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	page.Title = strings.TrimSpace(r.FormValue("title"))
	page.Slug = strings.TrimSpace(r.FormValue("slug"))
	page.IsPublished = r.FormValue("is_published") != ""
	if page.IsPublished && page.PublishedAt == nil {
		now := time.Now()
		page.PublishedAt = &now
	}
	if page.Meta == nil {
		page.Meta = map[string]any{}
	}
	page.Meta["description"] = strings.TrimSpace(r.FormValue("meta_description"))
	if err := s.repos.Pages.Update(page); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Seite wurde aktualisiert.", "/admin/pages")
}

// --- Navigation ---

func (s *Server) adminNavigationList(w http.ResponseWriter, r *http.Request) {
	items, err := s.repos.Navigation.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Navigation", "navigation")
	view.Data = struct {
		Items     []models.NavigationItem
		Locations []models.NavigationLocation
	}{items, models.NavigationLocations()}
	s.renderAdmin(w, r, "navigation_list", view)
}

func (s *Server) adminNavigationForm(w http.ResponseWriter, r *http.Request) {
	view := s.adminBase(r, "Navigationspunkt", "navigation")
	data := struct {
		Item      *models.NavigationItem
		Locations []models.NavigationLocation
	}{Locations: models.NavigationLocations()}
	if idStr := r.PathValue("id"); idStr != "" {
		id, _ := strconv.ParseInt(idStr, 10, 64)
		item, err := s.repos.Navigation.FindByID(id)
		if err != nil {
			s.notFound(w, r)
			return
		}
		data.Item = item
	} else {
		data.Item = &models.NavigationItem{Location: models.NavHeader, IsVisible: true}
	}
	view.Data = data
	s.renderAdmin(w, r, "navigation_form", view)
}

func (s *Server) navFromForm(r *http.Request, n *models.NavigationItem) {
	n.Location = models.NavigationLocation(r.FormValue("location"))
	n.Label = strings.TrimSpace(r.FormValue("label"))
	n.URL = strings.TrimSpace(r.FormValue("url"))
	n.Anchor = optionalString(r.FormValue("anchor"))
	n.UmamiEventTarget = optionalString(r.FormValue("umami_event_target"))
	n.OpenInNewTab = r.FormValue("open_in_new_tab") != ""
	n.IsCTA = r.FormValue("is_cta") != ""
	n.IsVisible = r.FormValue("is_visible") != ""
	if v := r.FormValue("condition"); v != "" {
		c := models.NavigationCondition(v)
		n.Condition = &c
	} else {
		n.Condition = nil
	}
	if v := r.FormValue("sort"); v != "" {
		n.Sort, _ = strconv.Atoi(v)
	}
}

func (s *Server) adminNavigationCreate(w http.ResponseWriter, r *http.Request) {
	var n models.NavigationItem
	s.navFromForm(r, &n)
	if err := s.repos.Navigation.Create(&n); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Navigationspunkt wurde erstellt.", "/admin/navigation")
}

func (s *Server) adminNavigationUpdate(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	n, err := s.repos.Navigation.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	s.navFromForm(r, n)
	if err := s.repos.Navigation.Update(n); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Navigationspunkt wurde aktualisiert.", "/admin/navigation")
}

func (s *Server) adminNavigationDelete(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	if err := s.repos.Navigation.Delete(id); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Navigationspunkt wurde gelöscht.", "/admin/navigation")
}

// --- Settings ---

func (s *Server) adminSettingsForm(w http.ResponseWriter, r *http.Request) {
	view := s.adminBase(r, "Einstellungen", "settings")
	view.Data = s.settings()
	s.renderAdmin(w, r, "settings_form", view)
}

func (s *Server) adminSettingsSave(w http.ResponseWriter, r *http.Request) {
	settings := s.settings()
	settings.SiteName = strings.TrimSpace(r.FormValue("site_name"))
	settings.SiteTagline = strings.TrimSpace(r.FormValue("site_tagline"))
	settings.SiteDescription = strings.TrimSpace(r.FormValue("site_description"))
	settings.ContactEmail = strings.TrimSpace(r.FormValue("contact_email"))
	settings.ContactPhone = optionalString(r.FormValue("contact_phone"))
	settings.Location = strings.TrimSpace(r.FormValue("location"))
	settings.WhatsAppCommunityLink = optionalString(r.FormValue("whatsapp_community_link"))
	settings.FooterText = strings.TrimSpace(r.FormValue("footer_text"))
	if v := r.FormValue("event_default_max_participants"); v != "" {
		settings.EventDefaultMaxParticipants, _ = strconv.Atoi(v)
	}
	if err := s.repos.Settings.Save(settings); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Einstellungen wurden gespeichert.", "/admin/settings")
}
