// Package notify composes and sends the application's emails, decoupling the
// HTTP handlers from the mail templates and delivery mechanism.
package notify

import (
	"html/template"
	"log/slog"
	"strings"

	"github.com/beardcoder/mens-circle/internal/mailer"
	"github.com/beardcoder/mens-circle/internal/models"
	"github.com/beardcoder/mens-circle/internal/repository"
	"github.com/beardcoder/mens-circle/internal/web"
)

// Service renders and sends emails.
type Service struct {
	mailer       mailer.Mailer
	render       *web.EmailRenderer
	repos        *repository.Repositories
	baseURL      string
	adminAddress string
	logger       *slog.Logger
}

// New constructs a notification service.
func New(m mailer.Mailer, render *web.EmailRenderer, repos *repository.Repositories, baseURL, adminAddress string, logger *slog.Logger) *Service {
	return &Service{
		mailer:       m,
		render:       render,
		repos:        repos,
		baseURL:      strings.TrimRight(baseURL, "/"),
		adminAddress: adminAddress,
		logger:       logger,
	}
}

// emailContext is the data passed to every email template.
type emailContext struct {
	Settings       *models.GeneralSettings
	BaseURL        string
	Subject        string
	FirstName      string
	Participant    *models.Participant
	Event          *models.Event
	IsWaitlist     bool
	IsToday        bool
	ContentHTML    template.HTML
	UnsubscribeURL string
}

func (s *Service) settings() *models.GeneralSettings {
	if settings, err := s.repos.Settings.Load(); err == nil {
		return settings
	}
	def := models.DefaultSettings()
	return &def
}

// send renders a template and delivers it, logging any failure.
func (s *Service) send(template, to, subject string, ctx emailContext) {
	ctx.Subject = subject
	html, err := s.render.Render(template, ctx)
	if err != nil {
		s.logger.Error("render email", "template", template, "err", err)
		return
	}
	if err := s.mailer.Send(mailer.Message{To: to, Subject: subject, HTML: html}); err != nil {
		s.logger.Error("send email", "template", template, "to", to, "err", err)
	}
}

func firstName(p *models.Participant) string {
	if p.FirstName != nil && strings.TrimSpace(*p.FirstName) != "" {
		return strings.TrimSpace(*p.FirstName)
	}
	return "Mann"
}

// RegistrationConfirmed notifies the participant and the admin (async).
func (s *Service) RegistrationConfirmed(p *models.Participant, e *models.Event) {
	go func() {
		settings := s.settings()
		ctx := emailContext{Settings: settings, BaseURL: s.baseURL, FirstName: firstName(p), Participant: p, Event: e}
		s.send("registration_confirmation", p.Email, "Anmeldebestätigung: "+e.Title, ctx)
		s.notifyAdmin(settings, p, e, false)
	}()
}

// Waitlisted notifies the participant and the admin (async).
func (s *Service) Waitlisted(p *models.Participant, e *models.Event) {
	go func() {
		settings := s.settings()
		ctx := emailContext{Settings: settings, BaseURL: s.baseURL, FirstName: firstName(p), Participant: p, Event: e, IsWaitlist: true}
		s.send("waitlist_confirmation", p.Email, "Warteliste: "+e.Title, ctx)
		s.notifyAdmin(settings, p, e, true)
	}()
}

// WaitlistPromoted notifies a participant that a seat opened up (async).
func (s *Service) WaitlistPromoted(p *models.Participant, e *models.Event) {
	go func() {
		ctx := emailContext{Settings: s.settings(), BaseURL: s.baseURL, FirstName: firstName(p), Participant: p, Event: e}
		s.send("waitlist_promotion", p.Email, "Ein Platz ist frei – "+e.Title, ctx)
	}()
}

func (s *Service) notifyAdmin(settings *models.GeneralSettings, p *models.Participant, e *models.Event, waitlist bool) {
	if s.adminAddress == "" {
		return
	}
	ctx := emailContext{Settings: settings, BaseURL: s.baseURL, Participant: p, Event: e, IsWaitlist: waitlist}
	subject := "Neue Anmeldung: " + e.Title
	if waitlist {
		subject = "Neue Wartelisten-Anmeldung: " + e.Title
	}
	s.send("admin_registration", s.adminAddress, subject, ctx)
}

// NewsletterWelcome sends the welcome email after a subscription (async).
func (s *Service) NewsletterWelcome(p *models.Participant, token string) {
	go func() {
		ctx := emailContext{
			Settings:       s.settings(),
			BaseURL:        s.baseURL,
			Participant:    p,
			UnsubscribeURL: s.baseURL + "/newsletter/unsubscribe/" + token,
		}
		s.send("newsletter_welcome", p.Email, "Willkommen beim "+ctx.Settings.SiteName+" Newsletter", ctx)
	}()
}

// EventReminder sends a single reminder synchronously (used by the scheduler).
func (s *Service) EventReminder(p *models.Participant, e *models.Event, isToday bool) error {
	settings := s.settings()
	ctx := emailContext{Settings: settings, BaseURL: s.baseURL, FirstName: firstName(p), Participant: p, Event: e, IsToday: isToday}
	subject := "Erinnerung: " + e.Title
	html, err := s.render.Render("event_reminder", ctx)
	if err != nil {
		return err
	}
	return s.mailer.Send(mailer.Message{To: p.Email, Subject: subject, HTML: html})
}

// SendNewsletter delivers a newsletter to all active subscribers, replacing
// placeholders per recipient. It returns the number successfully sent and runs
// synchronously (callers typically invoke it in a goroutine).
func (s *Service) SendNewsletter(n *models.Newsletter, recipients []repository.NewsletterRecipient) int {
	settings := s.settings()
	sent := 0
	for _, rcpt := range recipients {
		p := rcpt.Participant
		content := replacePlaceholders(n.Content, &p, settings)
		subject := replacePlaceholders(n.Subject, &p, settings)
		ctx := emailContext{
			Settings:       settings,
			BaseURL:        s.baseURL,
			Subject:        subject,
			ContentHTML:    template.HTML(toHTML(content)),
			UnsubscribeURL: s.baseURL + "/newsletter/unsubscribe/" + rcpt.Token,
		}
		html, err := s.render.Render("newsletter", ctx)
		if err != nil {
			s.logger.Error("render newsletter", "err", err)
			continue
		}
		if err := s.mailer.Send(mailer.Message{To: p.Email, Subject: subject, HTML: html}); err != nil {
			s.logger.Error("send newsletter", "to", p.Email, "err", err)
			continue
		}
		sent++
	}
	return sent
}

// replacePlaceholders substitutes the supported {tokens} in newsletter text.
func replacePlaceholders(text string, p *models.Participant, settings *models.GeneralSettings) string {
	fn := "Mann"
	if p.FirstName != nil && strings.TrimSpace(*p.FirstName) != "" {
		fn = strings.TrimSpace(*p.FirstName)
	}
	r := strings.NewReplacer(
		"{first_name}", fn,
		"{site_name}", settings.SiteName,
	)
	return r.Replace(text)
}

// toHTML converts plain newlines to <br> while leaving existing markup intact.
func toHTML(s string) string {
	if strings.Contains(s, "<") {
		return s
	}
	return strings.ReplaceAll(template.HTMLEscapeString(s), "\n", "<br>")
}
