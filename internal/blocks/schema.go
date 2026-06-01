// Package blocks defines the editable field schema for each content block
// type. The schema drives the admin block editor (which inputs to render) and
// is shared with the frontend as the contract for block data.
package blocks

// FieldKind identifies how a field is edited in the admin UI.
type FieldKind string

const (
	FieldText     FieldKind = "text"
	FieldTextarea FieldKind = "textarea"
	FieldHTML     FieldKind = "html"
	FieldImage    FieldKind = "image"
	FieldList     FieldKind = "list"
)

// Field describes a single editable property of a block.
type Field struct {
	Key    string    `json:"key"`
	Label  string    `json:"label"`
	Kind   FieldKind `json:"kind"`
	Fields []Field   `json:"fields,omitempty"` // sub-fields for list items
}

// Definition describes an editable block type.
type Definition struct {
	Type   string  `json:"type"`
	Label  string  `json:"label"`
	Fields []Field `json:"fields"`
}

func text(key, label string) Field     { return Field{Key: key, Label: label, Kind: FieldText} }
func textarea(key, label string) Field { return Field{Key: key, Label: label, Kind: FieldTextarea} }
func html(key, label string) Field     { return Field{Key: key, Label: label, Kind: FieldHTML} }
func image(key, label string) Field    { return Field{Key: key, Label: label, Kind: FieldImage} }

func list(key, label string, fields ...Field) Field {
	return Field{Key: key, Label: label, Kind: FieldList, Fields: fields}
}

var anchor = text("anchor", "Anker-ID (optional)")

// definitions is the ordered registry of editable block types.
var definitions = []Definition{
	{Type: "hero", Label: "Hero", Fields: []Field{
		text("label", "Label"),
		html("title", "Titel"),
		textarea("description", "Beschreibung"),
		text("button_text", "Button-Text"),
		text("button_link", "Button-Ziel (URL oder \"next-event\")"),
		image("background_image", "Hintergrundbild"),
		anchor,
	}},
	{Type: "intro", Label: "Intro", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		textarea("text", "Text"),
		html("quote", "Zitat"),
		anchor,
	}},
	{Type: "text_section", Label: "Textabschnitt", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		html("content", "Inhalt"),
		anchor,
	}},
	{Type: "value_items", Label: "Werte-Raster", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		list("items", "Werte", text("number", "Nummer"), text("title", "Titel"), textarea("description", "Beschreibung")),
		anchor,
	}},
	{Type: "journey_steps", Label: "Ablauf-Schritte", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		textarea("subtitle", "Untertitel"),
		list("steps", "Schritte", text("number", "Nummer"), text("title", "Titel"), textarea("description", "Beschreibung")),
		anchor,
	}},
	{Type: "moderator", Label: "Moderator", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("name", "Name"),
		html("bio", "Biografie"),
		textarea("quote", "Zitat"),
		image("photo", "Foto"),
		anchor,
	}},
	{Type: "cta", Label: "Call-to-Action", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		textarea("text", "Text"),
		text("button_text", "Button-Text"),
		text("button_link", "Button-Ziel"),
		anchor,
	}},
	{Type: "newsletter", Label: "Newsletter", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		textarea("text", "Text"),
		anchor,
	}},
	{Type: "whatsapp_community", Label: "WhatsApp-Community", Fields: []Field{
		anchor,
	}},
	{Type: "testimonials", Label: "Testimonials", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		anchor,
	}},
	{Type: "faq", Label: "FAQ", Fields: []Field{
		text("eyebrow", "Eyebrow"),
		html("title", "Titel"),
		textarea("intro", "Einleitung"),
		list("items", "Fragen", text("question", "Frage"), html("answer", "Antwort")),
		anchor,
	}},
}

// Definitions returns the ordered block type registry.
func Definitions() []Definition { return definitions }

// Definition returns the schema for a block type, or false if unknown.
func DefinitionFor(blockType string) (Definition, bool) {
	for _, d := range definitions {
		if d.Type == blockType {
			return d, true
		}
	}
	return Definition{}, false
}

// IsKnownType reports whether a block type has a registered schema.
func IsKnownType(blockType string) bool {
	_, ok := DefinitionFor(blockType)
	return ok
}
