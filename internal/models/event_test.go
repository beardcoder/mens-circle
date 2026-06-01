package models

import (
	"testing"
	"time"
)

func TestEventAvailableSpotsAndFull(t *testing.T) {
	e := Event{MaxParticipants: 8, ActiveRegistrations: 6}
	if got := e.AvailableSpots(); got != 2 {
		t.Fatalf("AvailableSpots = %d, want 2", got)
	}
	if e.IsFull() {
		t.Fatal("event should not be full")
	}

	e.ActiveRegistrations = 8
	if got := e.AvailableSpots(); got != 0 {
		t.Fatalf("AvailableSpots = %d, want 0", got)
	}
	if !e.IsFull() {
		t.Fatal("event should be full")
	}

	// Overbooking never yields negative free spots.
	e.ActiveRegistrations = 12
	if got := e.AvailableSpots(); got != 0 {
		t.Fatalf("AvailableSpots = %d, want 0 when overbooked", got)
	}
}

func TestEventIsPast(t *testing.T) {
	past := Event{EventDate: time.Now().AddDate(0, 0, -1)}
	if !past.IsPast() {
		t.Fatal("yesterday should be past")
	}
	future := Event{EventDate: time.Now().AddDate(0, 0, 1)}
	if future.IsPast() {
		t.Fatal("tomorrow should not be past")
	}
}

func TestEventFullAddress(t *testing.T) {
	street, postal, city := "Bahnhofstr. 1", "94315", "Straubing"
	e := Event{Street: &street, PostalCode: &postal, City: &city}
	if got := e.FullAddress(); got != "Bahnhofstr. 1, 94315 Straubing" {
		t.Fatalf("FullAddress = %q", got)
	}
	if (Event{}).FullAddress() != "" {
		t.Fatal("empty address should be empty string")
	}
}

func TestRegistrationStatusLabels(t *testing.T) {
	cases := map[RegistrationStatus]string{
		RegistrationRegistered: "Angemeldet",
		RegistrationWaitlist:   "Warteliste",
		RegistrationCancelled:  "Abgesagt",
		RegistrationAttended:   "Teilgenommen",
	}
	for status, want := range cases {
		if got := status.Label(); got != want {
			t.Errorf("%s.Label() = %q, want %q", status, got, want)
		}
	}
	if !RegistrationRegistered.IsActive() || !RegistrationAttended.IsActive() {
		t.Error("registered/attended should be active")
	}
	if RegistrationCancelled.IsActive() {
		t.Error("cancelled should not be active")
	}
}

func TestParticipantFullName(t *testing.T) {
	first, last := "Max", "Mustermann"
	p := Participant{FirstName: &first, LastName: &last, Email: "max@example.com"}
	if got := p.FullName(); got != "Max Mustermann" {
		t.Fatalf("FullName = %q", got)
	}
	empty := Participant{Email: "x@example.com"}
	if empty.DisplayName() != "x@example.com" {
		t.Fatalf("DisplayName fallback = %q", empty.DisplayName())
	}
}
