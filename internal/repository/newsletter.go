package repository

import (
	"crypto/rand"
	"database/sql"
	"encoding/hex"
	"errors"
	"time"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type NewsletterRepository struct{ db *database.DB }

const newsletterColumns = `id, subject, content, status, sent_at, recipient_count, created_at, updated_at`

func scanNewsletter(row interface{ Scan(...any) error }) (*models.Newsletter, error) {
	var n models.Newsletter
	var status string
	var sentAt sql.NullTime
	if err := row.Scan(&n.ID, &n.Subject, &n.Content, &status, &sentAt, &n.RecipientCount, &n.CreatedAt, &n.UpdatedAt); err != nil {
		return nil, err
	}
	n.Status = models.NewsletterStatus(status)
	n.SentAt = ptrTime(sentAt)
	return &n, nil
}

func (r *NewsletterRepository) FindByID(id int64) (*models.Newsletter, error) {
	row := r.db.QueryRow(`SELECT `+newsletterColumns+` FROM newsletters WHERE id = ?`, id)
	n, err := scanNewsletter(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return n, err
}

func (r *NewsletterRepository) All() ([]models.Newsletter, error) {
	rows, err := r.db.Query(`SELECT ` + newsletterColumns + ` FROM newsletters ORDER BY created_at DESC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.Newsletter
	for rows.Next() {
		n, err := scanNewsletter(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *n)
	}
	return out, rows.Err()
}

func (r *NewsletterRepository) Create(n *models.Newsletter) error {
	if n.Status == "" {
		n.Status = models.NewsletterDraft
	}
	res, err := r.db.Exec(`INSERT INTO newsletters (subject, content, status) VALUES (?, ?, ?)`,
		n.Subject, n.Content, string(n.Status))
	if err != nil {
		return err
	}
	n.ID, _ = res.LastInsertId()
	return nil
}

func (r *NewsletterRepository) Update(n *models.Newsletter) error {
	_, err := r.db.Exec(`UPDATE newsletters SET subject = ?, content = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		n.Subject, n.Content, string(n.Status), n.ID)
	return err
}

func (r *NewsletterRepository) MarkSent(id int64, recipientCount int) error {
	_, err := r.db.Exec(`UPDATE newsletters SET status = 'sent', sent_at = ?, recipient_count = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		time.Now(), recipientCount, id)
	return err
}

func (r *NewsletterRepository) SetStatus(id int64, status models.NewsletterStatus) error {
	_, err := r.db.Exec(`UPDATE newsletters SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`, string(status), id)
	return err
}

func (r *NewsletterRepository) Delete(id int64) error {
	_, err := r.db.Exec(`DELETE FROM newsletters WHERE id = ?`, id)
	return err
}

// --- Subscriptions ---

type SubscriptionRepository struct{ db *database.DB }

const subColumns = `id, participant_id, token, subscribed_at, confirmed_at, unsubscribed_at, created_at, updated_at, deleted_at`

func scanSubscription(row interface{ Scan(...any) error }) (*models.NewsletterSubscription, error) {
	var s models.NewsletterSubscription
	var confirmedAt, unsubscribedAt, deletedAt sql.NullTime
	if err := row.Scan(&s.ID, &s.ParticipantID, &s.Token, &s.SubscribedAt, &confirmedAt, &unsubscribedAt,
		&s.CreatedAt, &s.UpdatedAt, &deletedAt); err != nil {
		return nil, err
	}
	s.ConfirmedAt = ptrTime(confirmedAt)
	s.UnsubscribedAt = ptrTime(unsubscribedAt)
	s.DeletedAt = ptrTime(deletedAt)
	return &s, nil
}

// FindByParticipant returns a subscription (including soft-deleted) for a
// participant, or ErrNotFound.
func (r *SubscriptionRepository) FindByParticipant(participantID int64) (*models.NewsletterSubscription, error) {
	row := r.db.QueryRow(`SELECT `+subColumns+` FROM newsletter_subscriptions WHERE participant_id = ?`, participantID)
	s, err := scanSubscription(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return s, err
}

// FindByToken returns a subscription by its unsubscribe token.
func (r *SubscriptionRepository) FindByToken(token string) (*models.NewsletterSubscription, error) {
	row := r.db.QueryRow(`SELECT `+subColumns+` FROM newsletter_subscriptions WHERE token = ? AND deleted_at IS NULL`, token)
	s, err := scanSubscription(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return s, err
}

// Create inserts a new subscription, generating a token if none is set.
func (r *SubscriptionRepository) Create(s *models.NewsletterSubscription) error {
	if s.Token == "" {
		s.Token = GenerateToken()
	}
	if s.SubscribedAt.IsZero() {
		s.SubscribedAt = time.Now()
	}
	res, err := r.db.Exec(`INSERT INTO newsletter_subscriptions (participant_id, token, subscribed_at) VALUES (?, ?, ?)`,
		s.ParticipantID, s.Token, s.SubscribedAt)
	if err != nil {
		return err
	}
	s.ID, _ = res.LastInsertId()
	return nil
}

// Unsubscribe marks the subscription inactive.
func (r *SubscriptionRepository) Unsubscribe(id int64) error {
	_, err := r.db.Exec(`UPDATE newsletter_subscriptions SET unsubscribed_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`, time.Now(), id)
	return err
}

// Resubscribe reactivates a previously cancelled subscription.
func (r *SubscriptionRepository) Resubscribe(id int64) error {
	_, err := r.db.Exec(`UPDATE newsletter_subscriptions SET subscribed_at = ?, unsubscribed_at = NULL, deleted_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?`, time.Now(), id)
	return err
}

// ActiveCount returns the number of active subscriptions.
func (r *SubscriptionRepository) ActiveCount() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM newsletter_subscriptions WHERE unsubscribed_at IS NULL AND deleted_at IS NULL`).Scan(&n)
	return n, err
}

// ActiveParticipants returns participants with an active subscription, for
// newsletter sending.
func (r *SubscriptionRepository) ActiveParticipants() ([]models.Participant, error) {
	rows, err := r.db.Query(`SELECT p.id, p.first_name, p.last_name, p.email, p.phone, p.created_at, p.updated_at
		FROM participants p
		JOIN newsletter_subscriptions s ON s.participant_id = p.id
		WHERE s.unsubscribed_at IS NULL AND s.deleted_at IS NULL`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.Participant
	for rows.Next() {
		p, err := scanParticipant(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *p)
	}
	return out, rows.Err()
}

// GenerateToken returns a 64-character random hex token.
func GenerateToken() string {
	b := make([]byte, 32)
	_, _ = rand.Read(b)
	return hex.EncodeToString(b)
}
