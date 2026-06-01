package models

import "time"

// Page is a CMS page composed of ordered content blocks.
type Page struct {
	ID          int64          `json:"id"`
	Title       string         `json:"title"`
	Slug        string         `json:"slug"`
	Meta        map[string]any `json:"meta"`
	IsPublished bool           `json:"is_published"`
	PublishedAt *time.Time     `json:"published_at"`
	CreatedAt   time.Time      `json:"created_at"`
	UpdatedAt   time.Time      `json:"updated_at"`
	DeletedAt   *time.Time     `json:"deleted_at"`

	ContentBlocks []ContentBlock `json:"content_blocks,omitempty"`
}

// MetaString returns a string value from the page meta, or "".
func (p Page) MetaString(key string) string {
	if p.Meta == nil {
		return ""
	}
	if v, ok := p.Meta[key].(string); ok {
		return v
	}
	return ""
}

// ContentBlockType enumerates the supported block renderers.
type ContentBlockType string

const (
	BlockHero              ContentBlockType = "hero"
	BlockTextSection       ContentBlockType = "text_section"
	BlockIntro             ContentBlockType = "intro"
	BlockValueItems        ContentBlockType = "value_items"
	BlockJourneySteps      ContentBlockType = "journey_steps"
	BlockModerator         ContentBlockType = "moderator"
	BlockCTA               ContentBlockType = "cta"
	BlockNewsletter        ContentBlockType = "newsletter"
	BlockWhatsAppCommunity ContentBlockType = "whatsapp_community"
	BlockTestimonials      ContentBlockType = "testimonials"
	BlockFAQ               ContentBlockType = "faq"
)

// ContentBlock is a single typed section of a page. The Data field holds the
// block-specific content as a free-form JSON object.
type ContentBlock struct {
	ID        int64            `json:"id"`
	PageID    *int64           `json:"page_id"`
	Type      ContentBlockType `json:"type"`
	Data      map[string]any   `json:"data"`
	BlockID   string           `json:"block_id"`
	Order     int              `json:"order"`
	CreatedAt time.Time        `json:"created_at"`
	UpdatedAt time.Time        `json:"updated_at"`
}

// String returns a Data string value, or "".
func (b ContentBlock) String(key string) string {
	if b.Data == nil {
		return ""
	}
	if v, ok := b.Data[key].(string); ok {
		return v
	}
	return ""
}

// Items returns a Data slice of objects (e.g. faq items, value items).
func (b ContentBlock) Items(key string) []map[string]any {
	if b.Data == nil {
		return nil
	}
	raw, ok := b.Data[key].([]any)
	if !ok {
		return nil
	}
	out := make([]map[string]any, 0, len(raw))
	for _, item := range raw {
		if m, ok := item.(map[string]any); ok {
			out = append(out, m)
		}
	}
	return out
}
