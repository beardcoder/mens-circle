package repository

import (
	"database/sql"
	"errors"
	"time"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type TestimonialRepository struct{ db *database.DB }

const testimonialColumns = `id, quote, author_name, email, role, is_published, published_at, sort_order, created_at, updated_at, deleted_at`

func scanTestimonial(row interface{ Scan(...any) error }) (*models.Testimonial, error) {
	var t models.Testimonial
	var author, email, role sql.NullString
	var publishedAt, deletedAt sql.NullTime
	if err := row.Scan(&t.ID, &t.Quote, &author, &email, &role, &t.IsPublished, &publishedAt, &t.SortOrder,
		&t.CreatedAt, &t.UpdatedAt, &deletedAt); err != nil {
		return nil, err
	}
	t.AuthorName = ptrString(author)
	t.Email = ptrString(email)
	t.Role = ptrString(role)
	t.PublishedAt = ptrTime(publishedAt)
	t.DeletedAt = ptrTime(deletedAt)
	return &t, nil
}

func (r *TestimonialRepository) query(where string, args ...any) ([]models.Testimonial, error) {
	rows, err := r.db.Query(`SELECT `+testimonialColumns+` FROM testimonials WHERE `+where+
		` ORDER BY sort_order ASC, created_at DESC`, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.Testimonial
	for rows.Next() {
		t, err := scanTestimonial(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *t)
	}
	return out, rows.Err()
}

// Published returns published testimonials in display order.
func (r *TestimonialRepository) Published() ([]models.Testimonial, error) {
	return r.query("is_published = 1 AND deleted_at IS NULL")
}

// All returns every non-deleted testimonial for the admin panel.
func (r *TestimonialRepository) All() ([]models.Testimonial, error) {
	return r.query("deleted_at IS NULL")
}

func (r *TestimonialRepository) FindByID(id int64) (*models.Testimonial, error) {
	row := r.db.QueryRow(`SELECT `+testimonialColumns+` FROM testimonials WHERE id = ? AND deleted_at IS NULL`, id)
	t, err := scanTestimonial(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return t, err
}

func (r *TestimonialRepository) Create(t *models.Testimonial) error {
	res, err := r.db.Exec(`INSERT INTO testimonials (quote, author_name, email, role, is_published, published_at, sort_order)
		VALUES (?, ?, ?, ?, ?, ?, ?)`,
		t.Quote, nullString(t.AuthorName), nullString(t.Email), nullString(t.Role), t.IsPublished, nullTime(t.PublishedAt), t.SortOrder)
	if err != nil {
		return err
	}
	t.ID, _ = res.LastInsertId()
	return nil
}

func (r *TestimonialRepository) Update(t *models.Testimonial) error {
	_, err := r.db.Exec(`UPDATE testimonials SET quote = ?, author_name = ?, email = ?, role = ?,
		is_published = ?, published_at = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		t.Quote, nullString(t.AuthorName), nullString(t.Email), nullString(t.Role),
		t.IsPublished, nullTime(t.PublishedAt), t.SortOrder, t.ID)
	return err
}

// SetPublished publishes or unpublishes a testimonial, stamping published_at.
func (r *TestimonialRepository) SetPublished(id int64, published bool) error {
	var publishedAt any
	if published {
		publishedAt = time.Now()
	}
	_, err := r.db.Exec(`UPDATE testimonials SET is_published = ?, published_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		published, publishedAt, id)
	return err
}

func (r *TestimonialRepository) SoftDelete(id int64) error {
	_, err := r.db.Exec(`UPDATE testimonials SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?`, id)
	return err
}

func (r *TestimonialRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM testimonials WHERE deleted_at IS NULL`).Scan(&n)
	return n, err
}
