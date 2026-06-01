package server

import (
	"encoding/json"
	"errors"
	"net/http"
	"net/mail"
	"strings"

	"github.com/beardcoder/mens-circle/internal/models"
	"github.com/beardcoder/mens-circle/internal/repository"
)

// jsonResponse is the shape every API endpoint returns.
type jsonResponse struct {
	Success  bool   `json:"success"`
	Waitlist bool   `json:"waitlist,omitempty"`
	Message  string `json:"message"`
}

func writeJSON(w http.ResponseWriter, status int, resp jsonResponse) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(resp)
}

func validEmail(email string) bool {
	_, err := mail.ParseAddress(email)
	return err == nil && len(email) <= 255
}

func firstName(name string) string {
	parts := strings.Fields(strings.TrimSpace(name))
	if len(parts) == 0 {
		return ""
	}
	return parts[0]
}

// apiRegisterEvent handles POST /api/event/register.
func (s *Server) apiRegisterEvent(w http.ResponseWriter, r *http.Request) {
	var in struct {
		EventID     int64  `json:"event_id"`
		FirstName   string `json:"first_name"`
		LastName    string `json:"last_name"`
		Email       string `json:"email"`
		PhoneNumber string `json:"phone_number"`
		Privacy     bool   `json:"privacy"`
	}
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil {
		writeJSON(w, http.StatusBadRequest, jsonResponse{Message: "Ungültige Anfrage."})
		return
	}

	switch {
	case strings.TrimSpace(in.FirstName) == "":
		writeJSON(w, 422, jsonResponse{Message: "Bitte gib deinen Vornamen ein."})
		return
	case strings.TrimSpace(in.LastName) == "":
		writeJSON(w, 422, jsonResponse{Message: "Bitte gib deinen Nachnamen ein."})
		return
	case !validEmail(in.Email):
		writeJSON(w, 422, jsonResponse{Message: "Bitte gib eine gültige E-Mail-Adresse ein."})
		return
	case !in.Privacy:
		writeJSON(w, 422, jsonResponse{Message: "Bitte bestätige die Datenschutzerklärung."})
		return
	}

	event, err := s.repos.Events.FindByID(in.EventID)
	if errors.Is(err, repository.ErrNotFound) || (err == nil && !event.IsPublished) {
		writeJSON(w, http.StatusNotFound, jsonResponse{Message: "Diese Veranstaltung ist nicht verfügbar."})
		return
	}
	if err != nil {
		writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
		return
	}
	if event.IsPast() {
		writeJSON(w, http.StatusGone, jsonResponse{Message: "Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich."})
		return
	}

	first, last := in.FirstName, in.LastName
	var phone *string
	if p := strings.TrimSpace(in.PhoneNumber); p != "" {
		phone = &p
	}
	participant, err := s.repos.Participants.FindOrCreateByEmail(in.Email, &first, &last, phone)
	if err != nil {
		writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
		return
	}

	// Reject an existing active registration.
	if existing, err := s.repos.Registrations.FindByParticipantAndEvent(participant.ID, event.ID); err == nil {
		msg := "Du bist bereits für diese Veranstaltung angemeldet."
		if existing.Status == models.RegistrationWaitlist {
			msg = "Du bist bereits auf der Warteliste für diese Veranstaltung."
		}
		writeJSON(w, http.StatusConflict, jsonResponse{Message: msg})
		return
	}

	status := models.RegistrationRegistered
	if event.IsFull() {
		status = models.RegistrationWaitlist
	}
	reg := &models.Registration{ParticipantID: participant.ID, EventID: event.ID, Status: status}
	if err := s.repos.Registrations.Create(reg); err != nil {
		writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
		return
	}

	fn := firstName(first + " " + last)
	if status == models.RegistrationWaitlist {
		writeJSON(w, http.StatusOK, jsonResponse{
			Success:  true,
			Waitlist: true,
			Message:  "Danke " + fn + "! Die Veranstaltung ist aktuell ausgebucht – du stehst jetzt auf der Warteliste und wir melden uns, sobald ein Platz frei wird.",
		})
		return
	}
	writeJSON(w, http.StatusOK, jsonResponse{
		Success: true,
		Message: "Vielen Dank " + fn + "! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.",
	})
}

// apiNewsletterSubscribe handles POST /api/newsletter/subscribe.
func (s *Server) apiNewsletterSubscribe(w http.ResponseWriter, r *http.Request) {
	var in struct {
		Email string `json:"email"`
	}
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil || !validEmail(in.Email) {
		writeJSON(w, 422, jsonResponse{Message: "Bitte gib eine gültige E-Mail-Adresse ein."})
		return
	}

	participant, err := s.repos.Participants.FindOrCreateByEmail(in.Email, nil, nil, nil)
	if err != nil {
		writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
		return
	}

	sub, err := s.repos.Subscriptions.FindByParticipant(participant.ID)
	switch {
	case err == nil && sub.IsActive():
		writeJSON(w, http.StatusConflict, jsonResponse{Message: "Diese E-Mail-Adresse ist bereits für den Newsletter angemeldet."})
		return
	case err == nil:
		if err := s.repos.Subscriptions.Resubscribe(sub.ID); err != nil {
			writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
			return
		}
	case errors.Is(err, repository.ErrNotFound):
		if err := s.repos.Subscriptions.Create(&models.NewsletterSubscription{ParticipantID: participant.ID}); err != nil {
			writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
			return
		}
	default:
		writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
		return
	}

	writeJSON(w, http.StatusOK, jsonResponse{Success: true, Message: "Vielen Dank! Du wurdest erfolgreich für den Newsletter angemeldet."})
}

// apiTestimonialSubmit handles POST /api/testimonial/submit.
func (s *Server) apiTestimonialSubmit(w http.ResponseWriter, r *http.Request) {
	var in struct {
		Quote      string `json:"quote"`
		AuthorName string `json:"author_name"`
		Role       string `json:"role"`
		Email      string `json:"email"`
		Privacy    bool   `json:"privacy"`
	}
	if err := json.NewDecoder(r.Body).Decode(&in); err != nil {
		writeJSON(w, http.StatusBadRequest, jsonResponse{Message: "Ungültige Anfrage."})
		return
	}

	quote := strings.TrimSpace(in.Quote)
	switch {
	case len([]rune(quote)) < 10:
		writeJSON(w, 422, jsonResponse{Message: "Deine Erfahrung sollte mindestens 10 Zeichen lang sein."})
		return
	case len([]rune(quote)) > 1000:
		writeJSON(w, 422, jsonResponse{Message: "Deine Erfahrung darf maximal 1000 Zeichen lang sein."})
		return
	case !validEmail(in.Email):
		writeJSON(w, 422, jsonResponse{Message: "Bitte gib eine gültige E-Mail-Adresse ein."})
		return
	case !in.Privacy:
		writeJSON(w, 422, jsonResponse{Message: "Bitte bestätige die Datenschutzerklärung."})
		return
	}

	t := &models.Testimonial{Quote: quote}
	if v := strings.TrimSpace(in.AuthorName); v != "" {
		t.AuthorName = &v
	}
	if v := strings.TrimSpace(in.Role); v != "" {
		t.Role = &v
	}
	if v := strings.TrimSpace(in.Email); v != "" {
		t.Email = &v
	}
	if err := s.repos.Testimonials.Create(t); err != nil {
		writeJSON(w, http.StatusInternalServerError, jsonResponse{Message: "Es ist ein Fehler aufgetreten."})
		return
	}

	msg := "Vielen Dank! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht."
	if fn := firstName(in.AuthorName); fn != "" {
		msg = "Vielen Dank, " + fn + "! Deine Erfahrung wurde erfolgreich eingereicht und wird nach Prüfung veröffentlicht."
	}
	writeJSON(w, http.StatusOK, jsonResponse{Success: true, Message: msg})
}
