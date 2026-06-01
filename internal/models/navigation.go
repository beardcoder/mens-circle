package models

import "time"

// NavigationItem is a single menu entry rendered in the header or footer.
type NavigationItem struct {
	ID               int64                `json:"id"`
	Location         NavigationLocation   `json:"location"`
	Label            string               `json:"label"`
	URL              string               `json:"url"`
	Anchor           *string              `json:"anchor"`
	Condition        *NavigationCondition `json:"condition"`
	OpenInNewTab     bool                 `json:"open_in_new_tab"`
	IsCTA            bool                 `json:"is_cta"`
	IsVisible        bool                 `json:"is_visible"`
	UmamiEventTarget *string              `json:"umami_event_target"`
	Sort             int                  `json:"sort"`
	CreatedAt        time.Time            `json:"created_at"`
	UpdatedAt        time.Time            `json:"updated_at"`
}

// Href returns the full link target including the optional anchor fragment.
func (n NavigationItem) Href() string {
	href := n.URL
	if n.Anchor != nil && *n.Anchor != "" {
		href += "#" + *n.Anchor
	}
	return href
}
