package web

import (
	"bytes"
	"html/template"
	"io/fs"
)

// EmailRenderer renders the embedded email templates by name.
type EmailRenderer struct {
	tmpl *template.Template
}

// NewEmailRenderer parses every template under templates/emails/.
func NewEmailRenderer(fsys fs.FS) (*EmailRenderer, error) {
	tmpl, err := template.New("emails").Funcs(FuncMap()).ParseFS(fsys, "templates/emails/*.html")
	if err != nil {
		return nil, err
	}
	return &EmailRenderer{tmpl: tmpl}, nil
}

// Render executes the named email template and returns the HTML body.
func (e *EmailRenderer) Render(name string, data any) (string, error) {
	var buf bytes.Buffer
	if err := e.tmpl.ExecuteTemplate(&buf, name, data); err != nil {
		return "", err
	}
	return buf.String(), nil
}
