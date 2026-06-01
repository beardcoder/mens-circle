package repository

import (
	"path/filepath"
	"testing"
	"time"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

func newTestRepos(t *testing.T) *Repositories {
	t.Helper()
	db, err := database.Open(filepath.Join(t.TempDir(), "test.db"))
	if err != nil {
		t.Fatalf("open db: %v", err)
	}
	t.Cleanup(func() { _ = db.Close() })
	return New(db)
}

func TestParticipantFindOrCreate(t *testing.T) {
	repos := newTestRepos(t)
	first := "Max"
	p, err := repos.Participants.FindOrCreateByEmail("max@example.com", &first, nil, nil)
	if err != nil {
		t.Fatalf("create: %v", err)
	}
	if p.ID == 0 {
		t.Fatal("expected an id")
	}

	// Second call returns the same participant.
	again, err := repos.Participants.FindOrCreateByEmail("max@example.com", nil, nil, nil)
	if err != nil {
		t.Fatalf("find: %v", err)
	}
	if again.ID != p.ID {
		t.Fatalf("expected same id, got %d and %d", p.ID, again.ID)
	}
}

func TestEventCreateAndCount(t *testing.T) {
	repos := newTestRepos(t)
	e := &models.Event{
		Title: "Test", Slug: "test", EventDate: time.Now().AddDate(0, 0, 7), StartTime: "19:00", EndTime: "21:00",
		Location: "Straubing", MaxParticipants: 8, CostBasis: "Auf Spendenbasis", IsPublished: true,
	}
	if err := repos.Events.Create(e); err != nil {
		t.Fatalf("create event: %v", err)
	}
	count, err := repos.Events.Count()
	if err != nil || count != 1 {
		t.Fatalf("count = %d err = %v", count, err)
	}

	found, err := repos.Events.FindBySlug("test")
	if err != nil {
		t.Fatalf("find by slug: %v", err)
	}
	if found.Title != "Test" {
		t.Fatalf("title = %q", found.Title)
	}
}

func TestRegistrationWaitlistPromotion(t *testing.T) {
	repos := newTestRepos(t)
	e := &models.Event{Title: "E", Slug: "e", EventDate: time.Now().AddDate(0, 0, 7), StartTime: "19:00", EndTime: "21:00", Location: "S", MaxParticipants: 1, CostBasis: "x", IsPublished: true}
	if err := repos.Events.Create(e); err != nil {
		t.Fatal(err)
	}

	p1, _ := repos.Participants.FindOrCreateByEmail("a@example.com", nil, nil, nil)
	p2, _ := repos.Participants.FindOrCreateByEmail("b@example.com", nil, nil, nil)

	if err := repos.Registrations.Create(&models.Registration{ParticipantID: p1.ID, EventID: e.ID, Status: models.RegistrationRegistered}); err != nil {
		t.Fatal(err)
	}
	if err := repos.Registrations.Create(&models.Registration{ParticipantID: p2.ID, EventID: e.ID, Status: models.RegistrationWaitlist}); err != nil {
		t.Fatal(err)
	}

	promoted, err := repos.Registrations.PromoteNextWaitlisted(e.ID)
	if err != nil {
		t.Fatalf("promote: %v", err)
	}
	if promoted.ParticipantID != p2.ID || promoted.Status != models.RegistrationRegistered {
		t.Fatalf("unexpected promotion: %+v", promoted)
	}
}

func TestNewsletterSubscriptionLifecycle(t *testing.T) {
	repos := newTestRepos(t)
	p, _ := repos.Participants.FindOrCreateByEmail("sub@example.com", nil, nil, nil)

	sub := &models.NewsletterSubscription{ParticipantID: p.ID}
	if err := repos.Subscriptions.Create(sub); err != nil {
		t.Fatal(err)
	}
	if sub.Token == "" {
		t.Fatal("expected generated token")
	}
	if n, _ := repos.Subscriptions.ActiveCount(); n != 1 {
		t.Fatalf("active count = %d, want 1", n)
	}

	if err := repos.Subscriptions.Unsubscribe(sub.ID); err != nil {
		t.Fatal(err)
	}
	if n, _ := repos.Subscriptions.ActiveCount(); n != 0 {
		t.Fatalf("active count after unsubscribe = %d, want 0", n)
	}
}

func TestSettingsRoundTrip(t *testing.T) {
	repos := newTestRepos(t)
	s := models.DefaultSettings()
	s.SiteName = "Mein Kreis"
	phone := "0123"
	s.ContactPhone = &phone
	if err := repos.Settings.Save(&s); err != nil {
		t.Fatal(err)
	}

	loaded, err := repos.Settings.Load()
	if err != nil {
		t.Fatal(err)
	}
	if loaded.SiteName != "Mein Kreis" {
		t.Fatalf("site name = %q", loaded.SiteName)
	}
	if loaded.ContactPhone == nil || *loaded.ContactPhone != "0123" {
		t.Fatalf("contact phone = %v", loaded.ContactPhone)
	}
}
