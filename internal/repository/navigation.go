package repository

import (
	"database/sql"
	"errors"

	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/models"
)

type NavigationRepository struct{ db *database.DB }

const navColumns = `id, location, label, url, anchor, condition, open_in_new_tab, is_cta, is_visible, umami_event_target, sort, created_at, updated_at`

func scanNavItem(row interface{ Scan(...any) error }) (*models.NavigationItem, error) {
	var n models.NavigationItem
	var location string
	var anchor, condition, umami sql.NullString
	if err := row.Scan(&n.ID, &location, &n.Label, &n.URL, &anchor, &condition, &n.OpenInNewTab, &n.IsCTA,
		&n.IsVisible, &umami, &n.Sort, &n.CreatedAt, &n.UpdatedAt); err != nil {
		return nil, err
	}
	n.Location = models.NavigationLocation(location)
	n.Anchor = ptrString(anchor)
	if condition.Valid {
		c := models.NavigationCondition(condition.String)
		n.Condition = &c
	}
	n.UmamiEventTarget = ptrString(umami)
	return &n, nil
}

// ForLocation returns visible navigation items for a location, sorted.
func (r *NavigationRepository) ForLocation(loc models.NavigationLocation) ([]models.NavigationItem, error) {
	rows, err := r.db.Query(`SELECT `+navColumns+` FROM navigation_items
		WHERE location = ? AND is_visible = 1 ORDER BY sort ASC, id ASC`, string(loc))
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.NavigationItem
	for rows.Next() {
		n, err := scanNavItem(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *n)
	}
	return out, rows.Err()
}

// All returns every navigation item for the admin panel.
func (r *NavigationRepository) All() ([]models.NavigationItem, error) {
	rows, err := r.db.Query(`SELECT ` + navColumns + ` FROM navigation_items ORDER BY location ASC, sort ASC, id ASC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	var out []models.NavigationItem
	for rows.Next() {
		n, err := scanNavItem(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, *n)
	}
	return out, rows.Err()
}

func (r *NavigationRepository) FindByID(id int64) (*models.NavigationItem, error) {
	row := r.db.QueryRow(`SELECT `+navColumns+` FROM navigation_items WHERE id = ?`, id)
	n, err := scanNavItem(row)
	if errors.Is(err, sql.ErrNoRows) {
		return nil, ErrNotFound
	}
	return n, err
}

func (r *NavigationRepository) Create(n *models.NavigationItem) error {
	res, err := r.db.Exec(`INSERT INTO navigation_items
		(location, label, url, anchor, condition, open_in_new_tab, is_cta, is_visible, umami_event_target, sort)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
		string(n.Location), n.Label, n.URL, nullString(n.Anchor), navCondition(n.Condition),
		n.OpenInNewTab, n.IsCTA, n.IsVisible, nullString(n.UmamiEventTarget), n.Sort)
	if err != nil {
		return err
	}
	n.ID, _ = res.LastInsertId()
	return nil
}

func (r *NavigationRepository) Update(n *models.NavigationItem) error {
	_, err := r.db.Exec(`UPDATE navigation_items SET location = ?, label = ?, url = ?, anchor = ?, condition = ?,
		open_in_new_tab = ?, is_cta = ?, is_visible = ?, umami_event_target = ?, sort = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
		string(n.Location), n.Label, n.URL, nullString(n.Anchor), navCondition(n.Condition),
		n.OpenInNewTab, n.IsCTA, n.IsVisible, nullString(n.UmamiEventTarget), n.Sort, n.ID)
	return err
}

func (r *NavigationRepository) Delete(id int64) error {
	_, err := r.db.Exec(`DELETE FROM navigation_items WHERE id = ?`, id)
	return err
}

func navCondition(c *models.NavigationCondition) any {
	if c == nil {
		return nil
	}
	return string(*c)
}
