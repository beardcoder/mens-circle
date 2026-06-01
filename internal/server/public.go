package server

import (
	"errors"
	"net/http"

	"github.com/beardcoder/mens-circle/internal/models"
	"github.com/beardcoder/mens-circle/internal/repository"
)

// publicView holds the data shared by every public page (layout chrome).
type publicView struct {
	Settings        *models.GeneralSettings
	HeaderNav       []models.NavigationItem
	FooterPrimary   []models.NavigationItem
	FooterContact   []models.NavigationItem
	FooterLegal     []models.NavigationItem
	HasNextEvent    bool
	NextEventURL    string
	Title           string
	MetaDescription string

	// Page-specific payload.
	Page         *models.Page
	Event        *models.Event
	Testimonials []models.Testimonial
	Message      string
}

func (s *Server) baseView() publicView {
	settings := s.settings()
	header, _ := s.repos.Navigation.ForLocation(models.NavHeader)
	fp, _ := s.repos.Navigation.ForLocation(models.NavFooterPrimary)
	fc, _ := s.repos.Navigation.ForLocation(models.NavFooterContact)
	fl, _ := s.repos.Navigation.ForLocation(models.NavFooterLegal)

	view := publicView{
		Settings:        settings,
		HeaderNav:       header,
		FooterPrimary:   fp,
		FooterContact:   fc,
		FooterLegal:     fl,
		Title:           settings.SiteName,
		MetaDescription: settings.SiteDescription,
	}
	if next, err := s.repos.Events.NextUpcoming(); err == nil {
		view.HasNextEvent = true
		view.NextEventURL = "/event/" + next.Slug
	}
	return view
}

func (s *Server) home(w http.ResponseWriter, r *http.Request) {
	s.renderPage(w, r, "home")
}

func (s *Server) pageShow(w http.ResponseWriter, r *http.Request) {
	s.renderPage(w, r, r.PathValue("slug"))
}

func (s *Server) renderPage(w http.ResponseWriter, r *http.Request, slug string) {
	page, err := s.repos.Pages.FindPublishedBySlug(slug)
	if errors.Is(err, repository.ErrNotFound) {
		s.notFound(w, r)
		return
	}
	if err != nil {
		s.serverError(w, r, err)
		return
	}

	view := s.baseView()
	view.Page = page
	view.Title = page.Title + " – " + view.Settings.SiteName
	if d := page.MetaString("description"); d != "" {
		view.MetaDescription = d
	}

	// Testimonials are only loaded when the page actually uses them.
	for _, b := range page.ContentBlocks {
		if b.Type == models.BlockTestimonials {
			view.Testimonials, _ = s.repos.Testimonials.Published()
			break
		}
	}
	s.render.Render(w, http.StatusOK, "public/page", view)
}

func (s *Server) eventNext(w http.ResponseWriter, r *http.Request) {
	next, err := s.repos.Events.NextUpcoming()
	if errors.Is(err, repository.ErrNotFound) {
		view := s.baseView()
		view.Title = "Kein Termin geplant – " + view.Settings.SiteName
		s.render.Render(w, http.StatusOK, "public/no-event", view)
		return
	}
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	http.Redirect(w, r, "/event/"+next.Slug, http.StatusFound)
}

func (s *Server) eventShow(w http.ResponseWriter, r *http.Request) {
	event, err := s.repos.Events.FindBySlug(r.PathValue("slug"))
	if errors.Is(err, repository.ErrNotFound) || (err == nil && !event.IsPublished) {
		s.notFound(w, r)
		return
	}
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.baseView()
	view.Event = event
	view.Title = event.Title + " – " + view.Settings.SiteName
	if event.Description != nil {
		view.MetaDescription = *event.Description
	}
	s.render.Render(w, http.StatusOK, "public/event", view)
}

func (s *Server) eventICal(w http.ResponseWriter, r *http.Request) {
	event, err := s.repos.Events.FindBySlug(r.PathValue("slug"))
	if err != nil {
		s.notFound(w, r)
		return
	}
	w.Header().Set("Content-Type", "text/calendar; charset=utf-8")
	w.Header().Set("Content-Disposition", "attachment; filename=\"event-"+event.Slug+".ics\"")
	_, _ = w.Write([]byte(event.GenerateICalContent(s.baseURL)))
}

func (s *Server) testimonialForm(w http.ResponseWriter, r *http.Request) {
	view := s.baseView()
	view.Title = "Teile deine Erfahrung – " + view.Settings.SiteName
	s.render.Render(w, http.StatusOK, "public/testimonial-form", view)
}

func (s *Server) newsletterUnsubscribe(w http.ResponseWriter, r *http.Request) {
	view := s.baseView()
	view.Title = "Newsletter abgemeldet – " + view.Settings.SiteName

	sub, err := s.repos.Subscriptions.FindByToken(r.PathValue("token"))
	if errors.Is(err, repository.ErrNotFound) {
		s.notFound(w, r)
		return
	}
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	if !sub.IsActive() {
		view.Message = "Diese E-Mail-Adresse wurde bereits vom Newsletter abgemeldet."
	} else {
		if err := s.repos.Subscriptions.Unsubscribe(sub.ID); err != nil {
			s.serverError(w, r, err)
			return
		}
		view.Message = "Du wurdest erfolgreich vom Newsletter abgemeldet."
	}
	s.render.Render(w, http.StatusOK, "public/newsletter-unsubscribed", view)
}

func (s *Server) notFound(w http.ResponseWriter, r *http.Request) {
	view := s.baseView()
	view.Title = "Seite nicht gefunden"
	s.render.Render(w, http.StatusNotFound, "public/error", view)
}

func (s *Server) serverError(w http.ResponseWriter, r *http.Request, err error) {
	s.logger.Error("server error", "path", r.URL.Path, "err", err)
	view := s.baseView()
	view.Title = "Fehler"
	view.Message = "Es ist ein Fehler aufgetreten."
	s.render.Render(w, http.StatusInternalServerError, "public/error", view)
}
