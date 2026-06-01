// Package mensapp embeds the web assets (templates and static files) so the
// compiled binary is fully self-contained.
package mensapp

import "embed"

// WebFS contains the templates and static assets under web/.
//
//go:embed web/templates web/static
var WebFS embed.FS
