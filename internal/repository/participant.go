package repository

import (
	"database/sql"
	"errors"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

// ErrNotFound is returned when a lookup yields no row.
var ErrNotFound = errors.New("record not found")

type ParticipantRepository struct{ db *database.DB }

const participantColumns = `id, first_name, last_name, email, phone, created_at, updated_at`

func scanParticipant(row interface{ Scan(...any) error }) (*models.Participant, error) {
	var p models.Participant
	var first, last, phone sql.NullString
	if err := row.Scan(&p.ID, &first, &last, &p.Email, &phone, &p.CreatedAt, &p.UpdatedAt); err != nil {
		return nil, err
	}
	p.FirstName = ptrString(first)
	p.LastName = ptrString(last)
	p.Phone = ptrString(phone)
	return &p, nil
}

// FindByEmail returns the participant with the given email, or ErrNotFound.
func (r *ParticipantRepository) FindByEmail(email string) (*models.Participant, error) {
	row := r.db.QueryRow(`SELECT `+participantColumns+` FROM participants WHERE email = ?`, email)
	p, err := scanParticipant(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return p, err
}

// FindByID returns the participant with the given id, or ErrNotFound.
func (r *ParticipantRepository) FindByID(id int64) (*models.Participant, error) {
	row := r.db.QueryRow(`SELECT `+participantColumns+` FROM participants WHERE id = ?`, id)
	p, err := scanParticipant(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return p, err
}

// FindOrCreateByEmail finds a participant by email, creating one (and updating
// the supplied attributes) if necessary.
func (r *ParticipantRepository) FindOrCreateByEmail(email string, firstName, lastName, phone *string) (*models.Participant, error) {
	existing, err := r.FindByEmail(email)
	if err == nil {
		// Update any newly provided attributes.
		if firstName != nil {
			existing.FirstName = firstName
		}
		if lastName != nil {
			existing.LastName = lastName
		}
		if phone != nil {
			existing.Phone = phone
		}
		if err := r.Update(existing); err != nil {
			return nil, err
		}
		return existing, nil
	}
	if !errors.Is(err, ErrNotFound) {
		return nil, err
	}

	p := &models.Participant{Email: email, FirstName: firstName, LastName: lastName, Phone: phone}
	if err := r.Create(p); err != nil {
		return nil, err
	}
	return p, nil
}

func (r *ParticipantRepository) Create(p *models.Participant) error {
	res, err := r.db.Exec(
		`INSERT INTO participants (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)`,
		nullString(p.FirstName), nullString(p.LastName), p.Email, nullString(p.Phone),
	)
	if err != nil {
		return err
	}
	p.ID, _ = res.LastInsertId()
	return nil
}

func (r *ParticipantRepository) Update(p *models.Participant) error {
	_, err := r.db.Exec(
		`UPDATE participants SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		nullString(p.FirstName), nullString(p.LastName), p.Email, nullString(p.Phone), p.ID,
	)
	return err
}

// All returns every participant ordered by creation date (newest first).
func (r *ParticipantRepository) All() ([]models.Participant, error) {
	rows, err := r.db.Query(`SELECT ` + participantColumns + ` FROM participants ORDER BY created_at DESC`)
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

// Count returns the total number of participants.
func (r *ParticipantRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM participants`).Scan(&n)
	return n, err
}
