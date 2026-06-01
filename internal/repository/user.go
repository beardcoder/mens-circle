package repository

import (
	"database/sql"
	"errors"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type UserRepository struct{ db *database.DB }

const userColumns = `id, name, email, email_verified_at, password, github_id, created_at, updated_at`

func scanUser(row interface{ Scan(...any) error }) (*models.User, error) {
	var u models.User
	var githubID sql.NullString
	var verifiedAt sql.NullTime
	if err := row.Scan(&u.ID, &u.Name, &u.Email, &verifiedAt, &u.PasswordHash, &githubID, &u.CreatedAt, &u.UpdatedAt); err != nil {
		return nil, err
	}
	u.GitHubID = ptrString(githubID)
	u.EmailVerifiedAt = ptrTime(verifiedAt)
	return &u, nil
}

func (r *UserRepository) FindByEmail(email string) (*models.User, error) {
	row := r.db.QueryRow(`SELECT `+userColumns+` FROM users WHERE email = ?`, email)
	u, err := scanUser(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return u, err
}

func (r *UserRepository) FindByID(id int64) (*models.User, error) {
	row := r.db.QueryRow(`SELECT `+userColumns+` FROM users WHERE id = ?`, id)
	u, err := scanUser(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return u, err
}

func (r *UserRepository) Create(u *models.User) error {
	res, err := r.db.Exec(`INSERT INTO users (name, email, password, github_id) VALUES (?, ?, ?, ?)`,
		u.Name, u.Email, u.PasswordHash, nullString(u.GitHubID))
	if err != nil {
		return err
	}
	u.ID, _ = res.LastInsertId()
	return nil
}

func (r *UserRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM users`).Scan(&n)
	return n, err
}
