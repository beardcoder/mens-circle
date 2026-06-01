package repository

import (
	"database/sql"
	"errors"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type MediaRepository struct{ db *database.DB }

const mediaColumns = `id, collection, name, file_name, mime_type, size, created_at`

func scanMedia(row interface{ Scan(...any) error }) (*models.Media, error) {
	var m models.Media
	var mime sql.NullString
	if err := row.Scan(&m.ID, &m.Collection, &m.Name, &m.FileName, &mime, &m.Size, &m.CreatedAt); err != nil {
		return nil, err
	}
	m.MimeType = ptrString(mime)
	return &m, nil
}

func (r *MediaRepository) Create(m *models.Media) error {
	if m.Collection == "" {
		m.Collection = "uploads"
	}
	res, err := r.db.Exec(`INSERT INTO media (collection, name, file_name, mime_type, size) VALUES (?, ?, ?, ?, ?)`,
		m.Collection, m.Name, m.FileName, nullString(m.MimeType), m.Size)
	if err != nil {
		return err
	}
	m.ID, _ = res.LastInsertId()
	return nil
}

func (r *MediaRepository) All() ([]models.Media, error) {
	rows, err := r.db.Query(`SELECT ` + mediaColumns + ` FROM media ORDER BY created_at DESC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.Media
	for rows.Next() {
		m, err := scanMedia(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *m)
	}
	return out, rows.Err()
}

func (r *MediaRepository) FindByID(id int64) (*models.Media, error) {
	row := r.db.QueryRow(`SELECT `+mediaColumns+` FROM media WHERE id = ?`, id)
	m, err := scanMedia(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return m, err
}

func (r *MediaRepository) Delete(id int64) error {
	_, err := r.db.Exec(`DELETE FROM media WHERE id = ?`, id)
	return err
}

func (r *MediaRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM media`).Scan(&n)
	return n, err
}
