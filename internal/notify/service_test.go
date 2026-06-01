package notify

import (
	"io/fs"
	"testing"
	"time"

	mensapp "github.com/beardcoder/mens-circle"
	"github.com/beardcoder/mens-circle/internal/models"
	"github.com/beardcoder/mens-circle/internal/web"
)

func emailRenderer(t *testing.T) *web.EmailRenderer {
	t.Helper()
	webFS, err := fs.Sub(mensapp.WebFS, "web")
	if err != nil {
		t.Fatalf("sub fs: %v", err)
	}
	r, err := web.NewEmailRenderer(webFS)
	if err != nil {
		t.Fatalf("email renderer: %v", err)
	}
	return r
}

func sampleContext() emailContext {
	settings := models.DefaultSettings()
	first := "Max"
	street, postal, city := "Bahnhofstr. 1", "94315", "Straubing"
	return emailContext{
		Settings:    &settings,
		BaseURL:     "https://example.com",
		Subject:     "Test",
		FirstName:   "Max",
		Participant: &models.Participant{FirstName: &first, Email: "max@example.com"},
		Event: &models.Event{
			Title: "Männerkreis", Slug: "maennerkreis", EventDate: time.Now().AddDate(0, 0, 3),
			StartTime: "19:00", EndTime: "21:00", Location: "Straubing", CostBasis: "Auf Spendenbasis",
			MaxParticipants: 8, Street: &street, PostalCode: &postal, City: &city,
		},
		UnsubscribeURL: "https://example.com/newsletter/unsubscribe/token",
		ContentHTML:    "<p>Hallo Welt</p>",
	}
}

func TestAllEmailTemplatesRender(t *testing.T) {
	r := emailRenderer(t)
	ctx := sampleContext()
	templates := []string{
		"registration_confirmation",
		"waitlist_confirmation",
		"waitlist_promotion",
		"admin_registration",
		"newsletter_welcome",
		"newsletter",
		"event_reminder",
	}
	for _, name := range templates {
		html, err := r.Render(name, ctx)
		if err != nil {
			t.Errorf("render %s: %v", name, err)
			continue
		}
		if len(html) < 50 {
			t.Errorf("render %s: output too small (%d bytes)", name, len(html))
		}
	}
}

func TestReplacePlaceholders(t *testing.T) {
	settings := models.DefaultSettings()
	settings.SiteName = "Kreis"
	first := "Hans"
	p := &models.Participant{FirstName: &first}
	got := replacePlaceholders("Hallo {first_name}, willkommen bei {site_name}!", p, &settings)
	want := "Hallo Hans, willkommen bei Kreis!"
	if got != want {
		t.Fatalf("got %q, want %q", got, want)
	}

	// Missing first name falls back to "Mann".
	got = replacePlaceholders("Hallo {first_name}", &models.Participant{}, &settings)
	if got != "Hallo Mann" {
		t.Fatalf("fallback got %q", got)
	}
}
