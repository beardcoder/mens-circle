package server

import (
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"

	"github.com/beardcoder/mens-circle/internal/blocks"
	"github.com/beardcoder/mens-circle/internal/models"
)

// blockInput is one block as serialised by the admin block editor.
type blockInput struct {
	ID      int64          `json:"id"`
	BlockID string         `json:"block_id"`
	Type    string         `json:"type"`
	Data    map[string]any `json:"data"`
}

// adminPageSaveBlocks persists the content blocks submitted by the editor.
// The editor sends the full ordered block set as JSON in the "blocks" field.
func (s *Server) adminPageSaveBlocks(w http.ResponseWriter, r *http.Request) {
	id, _ := strconv.ParseInt(r.PathValue("id"), 10, 64)
	page, err := s.repos.Pages.FindByID(id)
	if err != nil {
		s.notFound(w, r)
		return
	}

	var inputs []blockInput
	if err := json.Unmarshal([]byte(r.FormValue("blocks")), &inputs); err != nil {
		s.flashRedirect(w, r, "error", "Die Blöcke konnten nicht gelesen werden.", fmt.Sprintf("/admin/pages/%d/edit", id))
		return
	}

	out := make([]models.ContentBlock, 0, len(inputs))
	for i, in := range inputs {
		if !blocks.IsKnownType(in.Type) {
			continue
		}
		data := in.Data
		if data == nil {
			data = map[string]any{}
		}
		out = append(out, models.ContentBlock{
			ID:      in.ID,
			BlockID: in.BlockID,
			Type:    models.ContentBlockType(in.Type),
			Data:    data,
			Order:   i + 1,
		})
	}

	if err := s.repos.Pages.SaveBlocks(page.ID, out); err != nil {
		s.serverError(w, r, err)
		return
	}
	s.flashRedirect(w, r, "success", "Inhaltsblöcke wurden gespeichert.", fmt.Sprintf("/admin/pages/%d/edit", id))
}
