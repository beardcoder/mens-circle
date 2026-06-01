package models

// RegistrationStatus represents the lifecycle state of an event registration.
type RegistrationStatus string

const (
	RegistrationRegistered RegistrationStatus = "registered"
	RegistrationWaitlist   RegistrationStatus = "waitlist"
	RegistrationCancelled  RegistrationStatus = "cancelled"
	RegistrationAttended   RegistrationStatus = "attended"
)

// Label returns the German display label for the status.
func (s RegistrationStatus) Label() string {
	switch s {
	case RegistrationRegistered:
		return "Angemeldet"
	case RegistrationWaitlist:
		return "Warteliste"
	case RegistrationCancelled:
		return "Abgesagt"
	case RegistrationAttended:
		return "Teilgenommen"
	default:
		return string(s)
	}
}

// Color returns a UI color token used by the admin panel.
func (s RegistrationStatus) Color() string {
	switch s {
	case RegistrationRegistered:
		return "info"
	case RegistrationWaitlist:
		return "warning"
	case RegistrationCancelled:
		return "danger"
	case RegistrationAttended:
		return "success"
	default:
		return "gray"
	}
}

// IsActive reports whether the registration occupies a seat.
func (s RegistrationStatus) IsActive() bool {
	return s == RegistrationRegistered || s == RegistrationAttended
}

// RegistrationStatuses lists every status in display order.
func RegistrationStatuses() []RegistrationStatus {
	return []RegistrationStatus{
		RegistrationRegistered,
		RegistrationWaitlist,
		RegistrationCancelled,
		RegistrationAttended,
	}
}

// NewsletterStatus represents the sending state of a newsletter.
type NewsletterStatus string

const (
	NewsletterDraft   NewsletterStatus = "draft"
	NewsletterSending NewsletterStatus = "sending"
	NewsletterSent    NewsletterStatus = "sent"
)

func (s NewsletterStatus) Label() string {
	switch s {
	case NewsletterDraft:
		return "Entwurf"
	case NewsletterSending:
		return "Wird gesendet"
	case NewsletterSent:
		return "Gesendet"
	default:
		return string(s)
	}
}

func (s NewsletterStatus) Color() string {
	switch s {
	case NewsletterDraft:
		return "gray"
	case NewsletterSending:
		return "warning"
	case NewsletterSent:
		return "success"
	default:
		return "gray"
	}
}

func NewsletterStatuses() []NewsletterStatus {
	return []NewsletterStatus{NewsletterDraft, NewsletterSending, NewsletterSent}
}

// SocialLinkType identifies the kind of a social/contact link.
type SocialLinkType string

const (
	SocialEmail     SocialLinkType = "email"
	SocialPhone     SocialLinkType = "phone"
	SocialInstagram SocialLinkType = "instagram"
	SocialFacebook  SocialLinkType = "facebook"
	SocialTwitter   SocialLinkType = "twitter"
	SocialLinkedIn  SocialLinkType = "linkedin"
	SocialYouTube   SocialLinkType = "youtube"
	SocialWhatsApp  SocialLinkType = "whatsapp"
	SocialTelegram  SocialLinkType = "telegram"
	SocialWebsite   SocialLinkType = "website"
	SocialOther     SocialLinkType = "other"
)

func (t SocialLinkType) Label() string {
	switch t {
	case SocialEmail:
		return "E-Mail"
	case SocialPhone:
		return "Telefon"
	case SocialInstagram:
		return "Instagram"
	case SocialFacebook:
		return "Facebook"
	case SocialTwitter:
		return "Twitter (X)"
	case SocialLinkedIn:
		return "LinkedIn"
	case SocialYouTube:
		return "YouTube"
	case SocialWhatsApp:
		return "WhatsApp"
	case SocialTelegram:
		return "Telegram"
	case SocialWebsite:
		return "Website"
	default:
		return "Sonstiges"
	}
}

// NavigationLocation identifies where a navigation item is rendered.
type NavigationLocation string

const (
	NavHeader        NavigationLocation = "header"
	NavFooterPrimary NavigationLocation = "footer_primary"
	NavFooterContact NavigationLocation = "footer_contact"
	NavFooterLegal   NavigationLocation = "footer_legal"
)

func (l NavigationLocation) Label() string {
	switch l {
	case NavHeader:
		return "Header"
	case NavFooterPrimary:
		return "Footer – Navigation"
	case NavFooterContact:
		return "Footer – Kontakt"
	case NavFooterLegal:
		return "Footer – Rechtliches"
	default:
		return string(l)
	}
}

// UmamiEventName returns the analytics event name for the location.
func (l NavigationLocation) UmamiEventName() string {
	if l == NavHeader {
		return "nav-click"
	}
	return "footer-link"
}

func NavigationLocations() []NavigationLocation {
	return []NavigationLocation{NavHeader, NavFooterPrimary, NavFooterContact, NavFooterLegal}
}

// NavigationCondition gates whether a navigation item is shown.
type NavigationCondition string

const (
	NavConditionNextEvent NavigationCondition = "next_event"
)

func (c NavigationCondition) Label() string {
	switch c {
	case NavConditionNextEvent:
		return "Nur anzeigen, wenn ein nächster Termin existiert"
	default:
		return string(c)
	}
}
