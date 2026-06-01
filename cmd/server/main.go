// Command server runs the Männerkreis Go application: public site + admin panel.
package main

import (
	"context"
	"errors"
	"io/fs"
	"log/slog"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	mensapp "github.com/beardcoder/mens-circle"
	"github.com/beardcoder/mens-circle/internal/database"
	"github.com/beardcoder/mens-circle/internal/media"
	"github.com/beardcoder/mens-circle/internal/repository"
	"github.com/beardcoder/mens-circle/internal/seed"
	"github.com/beardcoder/mens-circle/internal/server"
	"github.com/beardcoder/mens-circle/internal/web"
)

func main() {
	logger := slog.New(slog.NewTextHandler(os.Stdout, &slog.HandlerOptions{Level: slog.LevelInfo}))

	if err := run(logger); err != nil {
		logger.Error("fatal", "err", err)
		os.Exit(1)
	}
}

func run(logger *slog.Logger) error {
	dbPath := env("DATABASE_PATH", "data/mens-circle.db")
	mediaPath := env("MEDIA_PATH", "data/media")
	addr := env("LISTEN_ADDR", ":8080")
	baseURL := env("APP_URL", "http://localhost:8080")

	if err := os.MkdirAll("data", 0o755); err != nil {
		return err
	}

	mediaStore, err := media.NewStore(mediaPath)
	if err != nil {
		return err
	}

	db, err := database.Open(dbPath)
	if err != nil {
		return err
	}
	defer db.Close()

	repos := repository.New(db)

	// Seed an admin user, settings and starter content on first run.
	if err := seed.Run(repos, logger); err != nil {
		return err
	}

	// Resolve the embedded web filesystem (rooted at web/).
	webFS, err := fs.Sub(mensapp.WebFS, "web")
	if err != nil {
		return err
	}
	staticFS, err := fs.Sub(webFS, "static")
	if err != nil {
		return err
	}

	renderer, err := web.NewRenderer(webFS)
	if err != nil {
		return err
	}

	sessions := web.NewSessionManager(repos.Users)
	srv := server.New(repos, sessions, renderer, staticFS, mediaStore, baseURL, logger)

	httpServer := &http.Server{
		Addr:              addr,
		Handler:           srv.Handler(),
		ReadHeaderTimeout: 10 * time.Second,
		WriteTimeout:      30 * time.Second,
		IdleTimeout:       120 * time.Second,
	}

	// Graceful shutdown on SIGINT/SIGTERM.
	go func() {
		stop := make(chan os.Signal, 1)
		signal.Notify(stop, syscall.SIGINT, syscall.SIGTERM)
		<-stop
		logger.Info("shutting down")
		ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
		defer cancel()
		_ = httpServer.Shutdown(ctx)
	}()

	logger.Info("server starting", "addr", addr, "url", baseURL, "admin", baseURL+"/admin")
	if err := httpServer.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
		return err
	}
	return nil
}

func env(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}
