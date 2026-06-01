package server

import (
	"net/http"

	"github.com/beardcoder/mens-circle/internal/models"
	"golang.org/x/crypto/bcrypt"
)

// adminView is the data passed to every admin template.
type adminView struct {
	Title     string
	Section   string // highlights the active nav entry
	User      *models.User
	FlashMsg  string
	FlashKind string
	Data      any
}

func (s *Server) adminBase(r *http.Request, title, section string) adminView {
	view := adminView{Title: title + " · Admin", Section: section}
	if id, ok := s.sessions.CurrentUserID(r); ok {
		if u, err := s.repos.Users.FindByID(id); err == nil {
			view.User = u
		}
	}
	view.FlashMsg, view.FlashKind = s.sessions.PopFlash(r)
	return view
}

func (s *Server) renderAdmin(w http.ResponseWriter, r *http.Request, name string, view adminView) {
	s.render.Render(w, http.StatusOK, "admin/"+name, view)
}

// --- Authentication ---

func (s *Server) adminLoginForm(w http.ResponseWriter, r *http.Request) {
	if _, ok := s.sessions.CurrentUserID(r); ok {
		http.Redirect(w, r, "/admin", http.StatusSeeOther)
		return
	}
	s.render.Render(w, http.StatusOK, "admin/login", adminView{Title: "Anmelden"})
}

func (s *Server) adminLogin(w http.ResponseWriter, r *http.Request) {
	email := r.FormValue("email")
	password := r.FormValue("password")

	user, err := s.repos.Users.FindByEmail(email)
	if err != nil || bcrypt.CompareHashAndPassword([]byte(user.PasswordHash), []byte(password)) != nil {
		s.render.Render(w, http.StatusUnauthorized, "admin/login", adminView{
			Title:     "Anmelden",
			FlashMsg:  "E-Mail-Adresse oder Passwort ist falsch.",
			FlashKind: "error",
		})
		return
	}
	s.sessions.Login(w, r, user.ID)
	http.Redirect(w, r, "/admin", http.StatusSeeOther)
}

func (s *Server) adminLogout(w http.ResponseWriter, r *http.Request) {
	s.sessions.Logout(w, r)
	http.Redirect(w, r, "/admin/login", http.StatusSeeOther)
}

// --- Dashboard ---

func (s *Server) adminDashboard(w http.ResponseWriter, r *http.Request) {
	type stats struct {
		Events        int
		Upcoming      int
		Registrations int
		Participants  int
		Subscribers   int
		Testimonials  int
		Recent        []models.Registration
	}
	st := stats{}
	st.Events, _ = s.repos.Events.Count()
	st.Upcoming, _ = s.repos.Events.UpcomingCount()
	st.Registrations, _ = s.repos.Registrations.Count()
	st.Participants, _ = s.repos.Participants.Count()
	st.Subscribers, _ = s.repos.Subscriptions.ActiveCount()
	st.Testimonials, _ = s.repos.Testimonials.Count()
	st.Recent, _ = s.repos.Registrations.ListWithRelations(8)

	view := s.adminBase(r, "Dashboard", "dashboard")
	view.Data = st
	s.renderAdmin(w, r, "dashboard", view)
}

func (s *Server) flashRedirect(w http.ResponseWriter, r *http.Request, kind, msg, url string) {
	s.sessions.SetFlash(r, kind, msg)
	http.Redirect(w, r, url, http.StatusSeeOther)
}
