# Makefile for the Go application (public site + admin panel).
#
# The repository also contains the legacy Laravel app, whose PHP `vendor/`
# directory makes the Go toolchain default to vendor mode. Exporting
# GOFLAGS=-mod=mod here keeps the Go build self-contained and portable without
# touching machine-level `go env`.
#
# Usage: make <target>  (e.g. make run, make build, make test)

export GOFLAGS = -mod=mod

BINARY := bin/server
PKG    := ./cmd/server

.PHONY: build run dev test vet fmt tidy clean

build: ## Compile the server binary
	go build -o $(BINARY) $(PKG)

run: ## Run the server (creates data/mens-circle.db on first run)
	go run $(PKG)

dev: ## Run with verbose defaults for local development
	ADMIN_PASSWORD=changeme go run $(PKG)

test: ## Run the test suite
	go test ./...

vet: ## Run go vet
	go vet ./...

fmt: ## Format all Go code
	gofmt -w internal cmd embed.go

tidy: ## Tidy module dependencies
	go mod tidy

clean: ## Remove build artefacts and the local database
	rm -rf $(BINARY) data
