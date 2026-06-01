// Package media stores uploaded files on the local filesystem.
package media

import (
	"crypto/rand"
	"encoding/hex"
	"fmt"
	"io"
	"mime/multipart"
	"os"
	"path/filepath"
	"strings"
)

// maxUploadSize caps a single upload at 10 MiB.
const maxUploadSize = 10 << 20

// allowedExt lists the image extensions accepted by the media library.
var allowedExt = map[string]bool{
	".jpg": true, ".jpeg": true, ".png": true, ".webp": true, ".gif": true, ".svg": true, ".avif": true,
}

// Store persists uploaded files under a base directory.
type Store struct {
	dir string
}

// NewStore creates the storage directory if needed and returns a Store.
func NewStore(dir string) (*Store, error) {
	if err := os.MkdirAll(dir, 0o755); err != nil {
		return nil, err
	}
	return &Store{dir: dir}, nil
}

// Dir returns the base storage directory (for serving files).
func (s *Store) Dir() string { return s.dir }

// SavedFile describes a stored upload.
type SavedFile struct {
	Name     string // original (sanitised) display name
	FileName string // unique name on disk
	MimeType string
	Size     int64
}

// Save writes an uploaded multipart file to disk under a unique name.
func (s *Store) Save(file multipart.File, header *multipart.FileHeader) (*SavedFile, error) {
	if header.Size > maxUploadSize {
		return nil, fmt.Errorf("die Datei ist zu groß (max. 10 MB)")
	}
	ext := strings.ToLower(filepath.Ext(header.Filename))
	if !allowedExt[ext] {
		return nil, fmt.Errorf("nur Bilddateien (jpg, png, webp, gif, svg, avif) sind erlaubt")
	}

	unique := randomToken() + ext
	dest := filepath.Join(s.dir, unique)

	out, err := os.Create(dest)
	if err != nil {
		return nil, err
	}
	defer out.Close()

	written, err := io.Copy(out, io.LimitReader(file, maxUploadSize+1))
	if err != nil {
		_ = os.Remove(dest)
		return nil, err
	}

	mimeType := header.Header.Get("Content-Type")
	if mimeType == "" {
		mimeType = "application/octet-stream"
	}
	return &SavedFile{
		Name:     sanitiseName(header.Filename),
		FileName: unique,
		MimeType: mimeType,
		Size:     written,
	}, nil
}

// Delete removes a stored file. A missing file is not an error.
func (s *Store) Delete(fileName string) error {
	if fileName == "" || strings.ContainsAny(fileName, "/\\") {
		return nil
	}
	err := os.Remove(filepath.Join(s.dir, fileName))
	if os.IsNotExist(err) {
		return nil
	}
	return err
}

func randomToken() string {
	b := make([]byte, 16)
	_, _ = rand.Read(b)
	return hex.EncodeToString(b)
}

func sanitiseName(name string) string {
	name = filepath.Base(name)
	name = strings.ReplaceAll(name, "\x00", "")
	if name == "" || name == "." {
		return "datei"
	}
	return name
}
