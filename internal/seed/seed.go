// Package seed populates the database with an admin user and starter content
// on first run, so a fresh deployment is immediately usable.
package seed

import (
	"log/slog"
	"os"
	"time"

	"github.com/beardcoder/mens-circle/internal/models"
	"github.com/beardcoder/mens-circle/internal/repository"
	"golang.org/x/crypto/bcrypt"
)

// Run seeds the database if it is empty.
func Run(repos *repository.Repositories, logger *slog.Logger) error {
	if err := seedAdmin(repos, logger); err != nil {
		return err
	}
	if err := seedSettings(repos); err != nil {
		return err
	}
	if err := seedNavigation(repos); err != nil {
		return err
	}
	if err := seedHomePage(repos); err != nil {
		return err
	}
	return seedTestimonials(repos)
}

func seedAdmin(repos *repository.Repositories, logger *slog.Logger) error {
	count, err := repos.Users.Count()
	if err != nil {
		return err
	}
	if count > 0 {
		return nil
	}

	email := env("ADMIN_EMAIL", "admin@mens-circle.de")
	password := env("ADMIN_PASSWORD", "changeme")
	hash, err := bcrypt.GenerateFromPassword([]byte(password), bcrypt.DefaultCost)
	if err != nil {
		return err
	}
	user := &models.User{Name: "Administrator", Email: email, PasswordHash: string(hash)}
	if err := repos.Users.Create(user); err != nil {
		return err
	}
	logger.Info("seeded admin user", "email", email, "password_source", passwordSource())
	return nil
}

func seedSettings(repos *repository.Repositories) error {
	settings, err := repos.Settings.Load()
	if err != nil {
		return err
	}
	// Persist defaults so the rows exist and are editable in the admin panel.
	return repos.Settings.Save(settings)
}

func ptr(s string) *string { return &s }

func seedNavigation(repos *repository.Repositories) error {
	existing, err := repos.Navigation.All()
	if err != nil {
		return err
	}
	if len(existing) > 0 {
		return nil
	}

	items := []models.NavigationItem{
		{Location: models.NavHeader, Label: "Ablauf", URL: "/", Anchor: ptr("journey"), IsVisible: true, Sort: 1},
		{Location: models.NavHeader, Label: "Stimmen", URL: "/", Anchor: ptr("testimonials"), IsVisible: true, Sort: 2},
		{Location: models.NavHeader, Label: "FAQ", URL: "/", Anchor: ptr("faq"), IsVisible: true, Sort: 3},
		{Location: models.NavHeader, Label: "Nächster Termin", URL: "/event", IsCTA: true, IsVisible: true, Sort: 4, Condition: cond(models.NavConditionNextEvent)},
		{Location: models.NavFooterPrimary, Label: "Startseite", URL: "/", IsVisible: true, Sort: 1},
		{Location: models.NavFooterPrimary, Label: "Termine", URL: "/event", IsVisible: true, Sort: 2},
		{Location: models.NavFooterPrimary, Label: "Erfahrung teilen", URL: "/teile-deine-erfahrung", IsVisible: true, Sort: 3},
		{Location: models.NavFooterLegal, Label: "Impressum", URL: "/impressum", IsVisible: true, Sort: 1},
		{Location: models.NavFooterLegal, Label: "Datenschutz", URL: "/datenschutz", IsVisible: true, Sort: 2},
	}
	for i := range items {
		if err := repos.Navigation.Create(&items[i]); err != nil {
			return err
		}
	}
	return nil
}

func cond(c models.NavigationCondition) *models.NavigationCondition { return &c }

func seedHomePage(repos *repository.Repositories) error {
	pages, err := repos.Pages.All()
	if err != nil {
		return err
	}
	for _, p := range pages {
		if p.Slug == "home" {
			return nil
		}
	}

	page := &models.Page{Title: "Startseite", Slug: "home", IsPublished: true}
	if err := repos.Pages.Create(page); err != nil {
		return err
	}

	blocks := []models.ContentBlock{
		{Type: models.BlockHero, Order: 1, Data: map[string]any{
			"label":       "Männerkreis Niederbayern / Straubing",
			"title":       "Gemeinsam wachsen.<br>Echte Verbindung.",
			"description": "Ein geschützter Raum für authentische Begegnung, persönliches Wachstum und ehrlichen Austausch unter Männern.",
			"button_text": "Nächster Termin",
			"button_link": "next-event",
		}},
		{Type: models.BlockIntro, Order: 2, Data: map[string]any{
			"eyebrow": "Worum es geht",
			"title":   "Ein Kreis. Echte Männer. Klare Präsenz.",
			"text":    "Im Männerkreis treffen wir uns regelmäßig, um uns auszutauschen, zuzuhören und uns gegenseitig zu stärken – ohne Bewertung, ohne Druck.",
			"quote":   "Allein gehst du schneller. Gemeinsam gehst du weiter.",
		}},
		{Type: models.BlockJourneySteps, Order: 3, Data: map[string]any{
			"eyebrow":  "Der Ablauf",
			"title":    "So läuft ein Abend ab",
			"subtitle": "Klar strukturiert und doch offen für das, was gerade lebendig ist.",
			"anchor":   "journey",
			"steps": []any{
				map[string]any{"number": "1", "title": "Ankommen", "description": "Wir kommen an, atmen durch und lassen den Alltag hinter uns."},
				map[string]any{"number": "2", "title": "Teilen", "description": "Jeder Mann bekommt Raum für das, was ihn bewegt."},
				map[string]any{"number": "3", "title": "Austausch", "description": "Wir hören zu, spiegeln und unterstützen einander."},
				map[string]any{"number": "4", "title": "Abschluss", "description": "Wir schließen den Kreis bewusst und gestärkt."},
			},
		}},
		{Type: models.BlockTestimonials, Order: 4, Data: map[string]any{
			"eyebrow": "Stimmen",
			"title":   "Was Männer über den Kreis sagen",
			"anchor":  "testimonials",
		}},
		{Type: models.BlockFAQ, Order: 5, Data: map[string]any{
			"eyebrow": "Häufige Fragen",
			"title":   "Gut zu wissen",
			"anchor":  "faq",
			"items": []any{
				map[string]any{"question": "Muss ich Erfahrung mitbringen?", "answer": "Nein. Der Kreis ist offen für alle Männer – egal ob zum ersten Mal oder erfahren."},
				map[string]any{"question": "Was kostet die Teilnahme?", "answer": "Die Treffen finden auf Spendenbasis statt."},
				map[string]any{"question": "Wie melde ich mich an?", "answer": "Über die jeweilige Event-Seite kannst du dich unkompliziert anmelden."},
			},
		}},
		{Type: models.BlockNewsletter, Order: 6, Data: map[string]any{
			"eyebrow": "Bleib in Verbindung",
			"title":   "Newsletter",
			"text":    "Erhalte Einladungen zu neuen Terminen direkt in dein Postfach.",
			"anchor":  "newsletter",
		}},
	}
	return repos.Pages.SaveBlocks(page.ID, blocks)
}

func seedTestimonials(repos *repository.Repositories) error {
	count, err := repos.Testimonials.Count()
	if err != nil {
		return err
	}
	if count > 0 {
		return nil
	}
	now := time.Now()
	items := []models.Testimonial{
		{Quote: "Der Männerkreis hat mir geholfen, ehrlicher mit mir selbst zu sein.", AuthorName: ptr("Michael"), Role: ptr("Teilnehmer"), IsPublished: true, PublishedAt: &now, SortOrder: 1},
		{Quote: "Ein Raum, in dem ich wirklich gehört werde. Das gibt es selten.", AuthorName: ptr("Thomas"), Role: ptr("Teilnehmer"), IsPublished: true, PublishedAt: &now, SortOrder: 2},
	}
	for i := range items {
		if err := repos.Testimonials.Create(&items[i]); err != nil {
			return err
		}
	}
	return nil
}

func env(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

func passwordSource() string {
	if os.Getenv("ADMIN_PASSWORD") != "" {
		return "env"
	}
	return "default (changeme)"
}
