package repository

import (
	"database/sql"
	"encoding/json"
	"errors"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type PageRepository struct{ db *database.DB }

const pageColumns = `id, title, slug, meta, is_published, published_at, created_at, updated_at, deleted_at`

func scanPage(row interface{ Scan(...any) error }) (*models.Page, error) {
	var p models.Page
	var meta sql.NullString
	var publishedAt, deletedAt sql.NullTime
	if err := row.Scan(&p.ID, &p.Title, &p.Slug, &meta, &p.IsPublished, &publishedAt, &p.CreatedAt, &p.UpdatedAt, &deletedAt); err != nil {
		return nil, err
	}
	if meta.Valid && meta.String != "" {
		_ = json.Unmarshal([]byte(meta.String), &p.Meta)
	}
	p.PublishedAt = ptrTime(publishedAt)
	p.DeletedAt = ptrTime(deletedAt)
	return &p, nil
}

// FindPublishedBySlug loads a published page together with its content blocks.
func (r *PageRepository) FindPublishedBySlug(slug string) (*models.Page, error) {
	row := r.db.QueryRow(`SELECT `+pageColumns+` FROM pages WHERE slug = ? AND is_published = 1 AND deleted_at IS NULL`, slug)
	p, err := scanPage(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	if err != nil {
		return nil, err
	}
	blocks, err := r.blocksForPage(p.ID)
	if err != nil {
		return nil, err
	}
	p.ContentBlocks = blocks
	return p, nil
}

func (r *PageRepository) FindByID(id int64) (*models.Page, error) {
	row := r.db.QueryRow(`SELECT `+pageColumns+` FROM pages WHERE id = ? AND deleted_at IS NULL`, id)
	p, err := scanPage(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	if err != nil {
		return nil, err
	}
	blocks, err := r.blocksForPage(p.ID)
	if err != nil {
		return nil, err
	}
	p.ContentBlocks = blocks
	return p, nil
}

func (r *PageRepository) blocksForPage(pageID int64) ([]models.ContentBlock, error) {
	rows, err := r.db.Query(`SELECT id, page_id, type, data, block_id, "order", created_at, updated_at
		FROM content_blocks WHERE page_id = ? ORDER BY "order" ASC`, pageID)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.ContentBlock
	for rows.Next() {
		var b models.ContentBlock
		var pageIDVal sql.NullInt64
		var typ, data string
		if err := rows.Scan(&b.ID, &pageIDVal, &typ, &data, &b.BlockID, &b.Order, &b.CreatedAt, &b.UpdatedAt); err != nil {
			return nil, err
		}
		if pageIDVal.Valid {
			b.PageID = &pageIDVal.Int64
		}
		b.Type = models.ContentBlockType(typ)
		_ = json.Unmarshal([]byte(data), &b.Data)
		out = append(out, b)
	}
	return out, rows.Err()
}

// All returns every non-deleted page for the admin panel.
func (r *PageRepository) All() ([]models.Page, error) {
	rows, err := r.db.Query(`SELECT ` + pageColumns + ` FROM pages WHERE deleted_at IS NULL ORDER BY title ASC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.Page
	for rows.Next() {
		p, err := scanPage(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *p)
	}
	return out, rows.Err()
}

func (r *PageRepository) Create(p *models.Page) error {
	meta, _ := json.Marshal(p.Meta)
	res, err := r.db.Exec(`INSERT INTO pages (title, slug, meta, is_published, published_at) VALUES (?, ?, ?, ?, ?)`,
		p.Title, p.Slug, string(meta), p.IsPublished, nullTime(p.PublishedAt))
	if err != nil {
		return err
	}
	p.ID, _ = res.LastInsertId()
	return nil
}

func (r *PageRepository) Update(p *models.Page) error {
	meta, _ := json.Marshal(p.Meta)
	_, err := r.db.Exec(`UPDATE pages SET title = ?, slug = ?, meta = ?, is_published = ?, published_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		p.Title, p.Slug, string(meta), p.IsPublished, nullTime(p.PublishedAt), p.ID)
	return err
}

func (r *PageRepository) SoftDelete(id int64) error {
	_, err := r.db.Exec(`UPDATE pages SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?`, id)
	return err
}

// SaveBlocks replaces a page's content blocks with the provided set in a single
// transaction, deleting any blocks no longer present.
func (r *PageRepository) SaveBlocks(pageID int64, blocks []models.ContentBlock) error {
	tx, err := r.db.Begin()
	if err != nil {
		return err
	}
	defer tx.Rollback() //nolint:errcheck

	kept := make([]any, 0, len(blocks))
	placeholders := ""
	for i := range blocks {
		b := &blocks[i]
		data, _ := json.Marshal(b.Data)
		if b.BlockID == "" {
			b.BlockID = GenerateToken()
		}
		if b.ID > 0 {
			if _, err := tx.Exec(`UPDATE content_blocks SET type = ?, data = ?, "order" = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
				string(b.Type), string(data), b.Order, b.ID); err != nil {
				return err
			}
		} else {
			res, err := tx.Exec(`INSERT INTO content_blocks (page_id, type, data, block_id, "order") VALUES (?, ?, ?, ?, ?)`,
				pageID, string(b.Type), string(data), b.BlockID, b.Order)
			if err != nil {
				return err
			}
			b.ID, _ = res.LastInsertId()
		}
		kept = append(kept, b.ID)
		if placeholders != "" {
			placeholders += ","
		}
		placeholders += "?"
	}

	// Delete blocks that are no longer part of the page.
	args := append([]any{pageID}, kept...)
	query := `DELETE FROM content_blocks WHERE page_id = ?`
	if len(kept) > 0 {
		query += ` AND id NOT IN (` + placeholders + `)`
	}
	if _, err := tx.Exec(query, args...); err != nil {
		return err
	}
	return tx.Commit()
}

func (r *PageRepository) Count() (int, error) {
	var n int
	err := r.db.QueryRow(`SELECT COUNT(*) FROM pages WHERE deleted_at IS NULL`).Scan(&n)
	return n, err
}
