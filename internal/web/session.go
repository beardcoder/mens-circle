// Package web provides HTTP-layer helpers: sessions, flash messages and
// template rendering shared by the public site and the admin panel.
package web

import (
	"net/http"
	"sync"
	"time"

	"github.com/beardcoder/mens-circle/internal/repository"
)

const sessionCookie = "mk_session"

// session is an in-memory authenticated session.
type session struct {
	userID    int64
	expiresAt time.Time
	flash     string
	flashKind string
}

// SessionManager is a simple in-memory session store guarding admin access.
// In-memory is sufficient for the single-instance deployment model; sessions
// reset on restart, requiring re-login.
type SessionManager struct {
	mu       sync.Mutex
	sessions map[string]*session
	users    *repository.UserRepository
	ttl      time.Duration
}

// NewSessionManager creates a session manager with an 8-hour TTL.
func NewSessionManager(users *repository.UserRepository) *SessionManager {
	m := &SessionManager{
		sessions: make(map[string]*session),
		users:    users,
		ttl:      8 * time.Hour,
	}
	go m.reap()
	return m
}

func (m *SessionManager) reap() {
	for range time.Tick(time.Hour) {
		m.mu.Lock()
		for id, s := range m.sessions {
			if time.Now().After(s.expiresAt) {
				delete(m.sessions, id)
			}
		}
		m.mu.Unlock()
	}
}

// Login creates a session for the user and sets the cookie.
func (m *SessionManager) Login(w http.ResponseWriter, r *http.Request, userID int64) {
	id := repository.GenerateToken()
	m.mu.Lock()
	m.sessions[id] = &session{userID: userID, expiresAt: time.Now().Add(m.ttl)}
	m.mu.Unlock()

	http.SetCookie(w, &http.Cookie{
		Name:     sessionCookie,
		Value:    id,
		Path:     "/",
		HttpOnly: true,
		SameSite: http.SameSiteLaxMode,
		Secure:   r.TLS != nil,
		Expires:  time.Now().Add(m.ttl),
	})
}

// Logout destroys the current session and clears the cookie.
func (m *SessionManager) Logout(w http.ResponseWriter, r *http.Request) {
	if c, err := r.Cookie(sessionCookie); err == nil {
		m.mu.Lock()
		delete(m.sessions, c.Value)
		m.mu.Unlock()
	}
	http.SetCookie(w, &http.Cookie{Name: sessionCookie, Value: "", Path: "/", MaxAge: -1})
}

// CurrentUserID returns the authenticated user id and whether a session exists.
func (m *SessionManager) CurrentUserID(r *http.Request) (int64, bool) {
	c, err := r.Cookie(sessionCookie)
	if err != nil {
		return 0, false
	}
	m.mu.Lock()
	defer m.mu.Unlock()
	s, ok := m.sessions[c.Value]
	if !ok || time.Now().After(s.expiresAt) {
		return 0, false
	}
	return s.userID, true
}

// SetFlash stores a one-shot message on the current session.
func (m *SessionManager) SetFlash(r *http.Request, kind, message string) {
	c, err := r.Cookie(sessionCookie)
	if err != nil {
		return
	}
	m.mu.Lock()
	defer m.mu.Unlock()
	if s, ok := m.sessions[c.Value]; ok {
		s.flash = message
		s.flashKind = kind
	}
}

// PopFlash returns and clears the current flash message.
func (m *SessionManager) PopFlash(r *http.Request) (string, string) {
	c, err := r.Cookie(sessionCookie)
	if err != nil {
		return "", ""
	}
	m.mu.Lock()
	defer m.mu.Unlock()
	s, ok := m.sessions[c.Value]
	if !ok {
		return "", ""
	}
	msg, kind := s.flash, s.flashKind
	s.flash, s.flashKind = "", ""
	return msg, kind
}
