// Package server wires together routing, handlers, middleware and rendering.
package server

import (
	"io/fs"
	"log/slog"
	"net/http"

	"github.com/beardcoder/mens-circle/internal/models"
	"github.com/beardcoder/mens-circle/internal/repository"
	"github.com/beardcoder/mens-circle/internal/web"
)

// Server holds the dependencies shared by all HTTP handlers.
type Server struct {
	repos    *repository.Repositories
	sessions *web.SessionManager
	render   *web.Renderer
	logger   *slog.Logger
	staticFS fs.FS
	baseURL  string
}

// New constructs a Server.
func New(repos *repository.Repositories, sessions *web.SessionManager, render *web.Renderer, staticFS fs.FS, baseURL string, logger *slog.Logger) *Server {
	return &Server{
		repos:    repos,
		sessions: sessions,
		render:   render,
		logger:   logger,
		staticFS: staticFS,
		baseURL:  baseURL,
	}
}

// Handler returns the fully configured HTTP handler.
func (s *Server) Handler() http.Handler {
	mux := http.NewServeMux()

	// Static assets.
	mux.Handle("GET /static/", http.StripPrefix("/static/", http.FileServer(http.FS(s.staticFS))))

	// Public JSON API.
	mux.HandleFunc("POST /api/event/register", s.apiRegisterEvent)
	mux.HandleFunc("POST /api/newsletter/subscribe", s.apiNewsletterSubscribe)
	mux.HandleFunc("POST /api/testimonial/submit", s.apiTestimonialSubmit)

	// Public pages.
	mux.HandleFunc("GET /{$}", s.home)
	mux.HandleFunc("GET /event", s.eventNext)
	mux.HandleFunc("GET /event/{slug}", s.eventShow)
	mux.HandleFunc("GET /event/{slug}/ical", s.eventICal)
	mux.HandleFunc("GET /teile-deine-erfahrung", s.testimonialForm)
	mux.HandleFunc("GET /newsletter/unsubscribe/{token}", s.newsletterUnsubscribe)

	// Admin authentication.
	mux.HandleFunc("GET /admin/login", s.adminLoginForm)
	mux.HandleFunc("POST /admin/login", s.adminLogin)
	mux.HandleFunc("POST /admin/logout", s.adminLogout)

	// Admin panel (auth-guarded).
	admin := func(pattern string, h http.HandlerFunc) {
		mux.Handle(pattern, s.requireAuth(h))
	}
	admin("GET /admin", s.adminDashboard)
	admin("GET /admin/{$}", s.adminDashboard)

	admin("GET /admin/events", s.adminEventsList)
	admin("GET /admin/events/new", s.adminEventForm)
	admin("POST /admin/events", s.adminEventCreate)
	admin("GET /admin/events/{id}/edit", s.adminEventForm)
	admin("POST /admin/events/{id}", s.adminEventUpdate)
	admin("POST /admin/events/{id}/delete", s.adminEventDelete)

	admin("GET /admin/registrations", s.adminRegistrationsList)
	admin("POST /admin/registrations/{id}/status", s.adminRegistrationStatus)

	admin("GET /admin/participants", s.adminParticipantsList)

	admin("GET /admin/testimonials", s.adminTestimonialsList)
	admin("GET /admin/testimonials/new", s.adminTestimonialForm)
	admin("POST /admin/testimonials", s.adminTestimonialCreate)
	admin("GET /admin/testimonials/{id}/edit", s.adminTestimonialForm)
	admin("POST /admin/testimonials/{id}", s.adminTestimonialUpdate)
	admin("POST /admin/testimonials/{id}/publish", s.adminTestimonialPublish)
	admin("POST /admin/testimonials/{id}/delete", s.adminTestimonialDelete)

	admin("GET /admin/newsletters", s.adminNewslettersList)
	admin("GET /admin/newsletters/new", s.adminNewsletterForm)
	admin("POST /admin/newsletters", s.adminNewsletterCreate)
	admin("GET /admin/newsletters/{id}/edit", s.adminNewsletterForm)
	admin("POST /admin/newsletters/{id}", s.adminNewsletterUpdate)
	admin("POST /admin/newsletters/{id}/send", s.adminNewsletterSend)

	admin("GET /admin/subscriptions", s.adminSubscriptionsList)

	admin("GET /admin/pages", s.adminPagesList)
	admin("GET /admin/pages/{id}/edit", s.adminPageForm)
	admin("POST /admin/pages/{id}", s.adminPageUpdate)

	admin("GET /admin/navigation", s.adminNavigationList)
	admin("GET /admin/navigation/new", s.adminNavigationForm)
	admin("POST /admin/navigation", s.adminNavigationCreate)
	admin("GET /admin/navigation/{id}/edit", s.adminNavigationForm)
	admin("POST /admin/navigation/{id}", s.adminNavigationUpdate)
	admin("POST /admin/navigation/{id}/delete", s.adminNavigationDelete)

	admin("GET /admin/settings", s.adminSettingsForm)
	admin("POST /admin/settings", s.adminSettingsSave)

	// CMS catch-all (must stay last).
	mux.HandleFunc("GET /{slug}", s.pageShow)

	return s.recoverer(s.requestLogger(mux))
}

// settings loads the current general settings, falling back to defaults.
func (s *Server) settings() *models.GeneralSettings {
	settings, err := s.repos.Settings.Load()
	if err != nil {
		s.logger.Error("load settings", "err", err)
		def := models.DefaultSettings()
		return &def
	}
	return settings
}
