package models

// SocialLink is a single configured social/contact link.
type SocialLink struct {
	Type  SocialLinkType `json:"type"`
	Label string         `json:"label"`
	Value string         `json:"value"`
}

// GeneralSettings holds the site-wide configuration, mirroring the Laravel
// Spatie settings ("general" group). It is persisted as key/value rows.
type GeneralSettings struct {
	SiteName                    string       `json:"site_name"`
	SiteTagline                 string       `json:"site_tagline"`
	SiteDescription             string       `json:"site_description"`
	ContactEmail                string       `json:"contact_email"`
	ContactPhone                *string      `json:"contact_phone"`
	Location                    string       `json:"location"`
	WhatsAppCommunityLink       *string      `json:"whatsapp_community_link"`
	SocialLinks                 []SocialLink `json:"social_links"`
	FooterText                  string       `json:"footer_text"`
	EventDefaultMaxParticipants int          `json:"event_default_max_participants"`
}

// DefaultSettings returns sensible defaults for a fresh installation.
func DefaultSettings() GeneralSettings {
	return GeneralSettings{
		SiteName:                    "Männerkreis Niederbayern / Straubing",
		SiteTagline:                 "Gemeinsam wachsen",
		SiteDescription:             "Ein Männerkreis für authentische Begegnung, persönliches Wachstum und echte Verbindung in Niederbayern.",
		ContactEmail:                "hallo@mens-circle.de",
		Location:                    "Straubing",
		FooterText:                  "Männerkreis Niederbayern / Straubing",
		EventDefaultMaxParticipants: 8,
		SocialLinks:                 []SocialLink{},
	}
}
