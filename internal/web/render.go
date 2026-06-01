package web

import (
	"bytes"
	"fmt"
	"html/template"
	"io/fs"
	"net/http"
	"strings"
	"time"
)

// Renderer holds parsed template sets for the admin panel and public site.
type Renderer struct {
	templates map[string]*template.Template
}

// FuncMap returns the template helper functions available in every template.
func FuncMap() template.FuncMap {
	return template.FuncMap{
		"germanDate": func(t time.Time) string {
			months := []string{"Januar", "Februar", "März", "April", "Mai", "Juni",
				"Juli", "August", "September", "Oktober", "November", "Dezember"}
			return fmt.Sprintf("%d. %s %d", t.Day(), months[t.Month()-1], t.Year())
		},
		"germanWeekday": func(t time.Time) string {
			days := []string{"Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"}
			return days[t.Weekday()]
		},
		"dateInput": func(t time.Time) string { return t.Format("2006-01-02") },
		"datetime":  func(t time.Time) string { return t.Format("02.01.2006 15:04") },
		"shortDate": func(t time.Time) string { return t.Format("02.01.2006") },
		"deref": func(s *string) string {
			if s == nil {
				return ""
			}
			return *s
		},
		"derefTime": func(t *time.Time) string {
			if t == nil {
				return "—"
			}
			return t.Format("02.01.2006 15:04")
		},
		"safeHTML": func(s string) template.HTML { return template.HTML(s) },
		"nl2br": func(s string) template.HTML {
			return template.HTML(strings.ReplaceAll(template.HTMLEscapeString(s), "\n", "<br>"))
		},
		"add":       func(a, b int) int { return a + b },
		"hasPrefix": strings.HasPrefix,
		"str":       func(v any) string { return fmt.Sprintf("%v", v) },
		"dict": func(values ...any) map[string]any {
			m := make(map[string]any, len(values)/2)
			for i := 0; i+1 < len(values); i += 2 {
				key, _ := values[i].(string)
				m[key] = values[i+1]
			}
			return m
		},
	}
}

// NewRenderer parses all page templates from the given filesystem. Each file
// under {area}/pages/*.html is parsed together with that area's layout so they
// can share the {{define "layout"}} shell.
func NewRenderer(fsys fs.FS) (*Renderer, error) {
	r := &Renderer{templates: map[string]*template.Template{}}
	for _, area := range []string{"admin", "public"} {
		layout := "templates/" + area + "/layout.html"
		partials, _ := fs.Glob(fsys, "templates/"+area+"/partials/*.html")
		pages, err := fs.Glob(fsys, "templates/"+area+"/pages/*.html")
		if err != nil {
			return nil, err
		}
		for _, page := range pages {
			name := area + "/" + baseName(page)
			files := append([]string{layout}, partials...)
			files = append(files, page)
			tmpl, err := template.New("layout.html").Funcs(FuncMap()).ParseFS(fsys, files...)
			if err != nil {
				return nil, fmt.Errorf("parse %s: %w", page, err)
			}
			r.templates[name] = tmpl
		}
	}
	return r, nil
}

func baseName(path string) string {
	idx := strings.LastIndex(path, "/")
	file := path[idx+1:]
	return strings.TrimSuffix(file, ".html")
}

// Render writes the named template ("admin/dashboard", "public/home", …).
func (r *Renderer) Render(w http.ResponseWriter, status int, name string, data any) {
	tmpl, ok := r.templates[name]
	if !ok {
		http.Error(w, "template not found: "+name, http.StatusInternalServerError)
		return
	}
	var buf bytes.Buffer
	if err := tmpl.ExecuteTemplate(&buf, "layout", data); err != nil {
		http.Error(w, "render error: "+err.Error(), http.StatusInternalServerError)
		return
	}
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.WriteHeader(status)
	_, _ = buf.WriteTo(w)
}
