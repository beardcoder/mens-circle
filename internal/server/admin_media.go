package server

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"github.com/beardcoder/mens-circle/internal/models"
)

func (s *Server) adminMediaList(w http.ResponseWriter, r *http.Request) {
	items, err := s.repos.Media.All()
	if err != nil {
		s.serverError(w, r, err)
		return
	}
	view := s.adminBase(r, "Medien", "media")
	view.Data = items
	s.renderAdmin(w, r, "media_list", view)
}

// adminMediaUpload handles a multipart upload. It responds with JSON when the
// caller expects it (the block editor), otherwise redirects to the library.
func (s *Server) adminMediaUpload(w http.ResponseWriter, r *http.Request) {
	wantsJSON := strings.Contains(r.Header.Get("Accept"), "application/json") ||
		r.Header.Get("X-Requested-With") == "fetch"

	if err := r.ParseMultipartForm(12 << 20); err != nil {
		s.mediaUploadError(w, r, wantsJSON, "Upload fehlgeschlagen.")
		return
	}
	file, header, err := r.FormFile("file")
	if err != nil {
		s.mediaUploadError(w, r, wantsJSON, "Bitte wähle eine Datei aus.")
		return
	}
	defer file.Close()

	saved, err := s.media.Save(file, header)
	if err != nil {
		s.mediaUploadError(w, r, wantsJSON, err.Error())
		return
	}

	mime := saved.MimeType
	m := &models.Media{
		Collection: "uploads",
		Name:       saved.Name,
		FileName:   saved.FileName,
		MimeType:   &mime,
		Size:       saved.Size,
	}
	if err := s.repos.Media.Create(m); err != nil {
		_ = s.media.Delete(saved.FileName)
		s.mediaUploadError(w, r, wantsJSON, "Speichern fehlgeschlagen.")
		return
	}

	if wantsJSON {
		writeJSONRaw(w, http.StatusOK, map[string]any{
			"success": true,
			"id":      m.ID,
			"url":     m.URL(),
			"name":    m.Name,
		})
		return
	}
	s.flashRedirect(w, r, "success", "Datei wurde hochgeladen.", "/admin/media")
}

func (s *Server) mediaUploadError(w http.ResponseWriter, r *http.Request, wantsJSON bool, msg string) {
	if wantsJSON {
		writeJSONRaw(w, http.StatusUnprocessableEntity, map[string]any{"success": false, "message": msg})
		return
	}
	s.flashRedirect(w, r, "error", msg, "/admin/media")
}

func (s *Server) adminMediaDelete(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	m, err := s.repos.Media.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}
	if err := s.repos.Media.Delete(id); err != nil {
		s.serverError(w, r, err)
		return
	}
	_ = s.media.Delete(m.FileName)
	s.flashRedirect(w, r, "success", "Datei wurde gelöscht.", "/admin/media")
}

func writeJSONRaw(w http.ResponseWriter, status int, payload any) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(payload)
}
