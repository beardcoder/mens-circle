// Package mailer sends transactional and bulk email. It supports real SMTP
// delivery (STARTTLS on 587, implicit TLS on 465, or plain) and a logging
// fallback used when no SMTP host is configured — mirroring Laravel's
// MAIL_MAILER=log default so the app is fully runnable without a mail server.
package mailer

import (
	"crypto/tls"
	"encoding/base64"
	"fmt"
	"log/slog"
	"net"
	"net/smtp"
	"strings"
	"time"
)

// Message is a single email to one recipient.
type Message struct {
	To      string
	Subject string
	HTML    string
}

// Mailer delivers messages.
type Mailer interface {
	Send(msg Message) error
	From() string
}

// Config describes SMTP connection and sender details.
type Config struct {
	Host        string
	Port        string
	Username    string
	Password    string
	Encryption  string // "tls" (STARTTLS), "ssl" (implicit), "none"
	FromAddress string
	FromName    string
}

// New returns an SMTP mailer when a host is configured, otherwise a LogMailer.
func New(cfg Config, logger *slog.Logger) Mailer {
	if strings.TrimSpace(cfg.Host) == "" {
		logger.Info("mailer: no SMTP host configured, using log mailer")
		return &LogMailer{logger: logger, from: cfg.FromAddress}
	}
	return &SMTPMailer{cfg: cfg, logger: logger}
}

// LogMailer logs emails instead of sending them.
type LogMailer struct {
	logger *slog.Logger
	from   string
}

func (m *LogMailer) From() string { return m.from }

func (m *LogMailer) Send(msg Message) error {
	m.logger.Info("mail (log)", "to", msg.To, "subject", msg.Subject, "bytes", len(msg.HTML))
	return nil
}

// SMTPMailer delivers via SMTP.
type SMTPMailer struct {
	cfg    Config
	logger *slog.Logger
}

func (m *SMTPMailer) From() string { return m.cfg.FromAddress }

func (m *SMTPMailer) Send(msg Message) error {
	addr := net.JoinHostPort(m.cfg.Host, m.cfg.port())
	raw := m.build(msg)

	var auth smtp.Auth
	if m.cfg.Username != "" {
		auth = smtp.PlainAuth("", m.cfg.Username, m.cfg.Password, m.cfg.Host)
	}

	if m.cfg.Encryption == "ssl" {
		return m.sendImplicitTLS(addr, auth, msg.To, raw)
	}
	return m.sendStartTLS(addr, auth, msg.To, raw)
}

// sendStartTLS connects in plaintext and upgrades via STARTTLS when offered.
func (m *SMTPMailer) sendStartTLS(addr string, auth smtp.Auth, to string, raw []byte) error {
	client, err := smtp.Dial(addr)
	if err != nil {
		return fmt.Errorf("smtp dial: %w", err)
	}
	defer client.Close()

	if ok, _ := client.Extension("STARTTLS"); ok {
		if err := client.StartTLS(&tls.Config{ServerName: m.cfg.Host}); err != nil {
			return fmt.Errorf("starttls: %w", err)
		}
	}
	return m.transmit(client, auth, to, raw)
}

// sendImplicitTLS opens a TLS connection directly (typically port 465).
func (m *SMTPMailer) sendImplicitTLS(addr string, auth smtp.Auth, to string, raw []byte) error {
	conn, err := tls.Dial("tcp", addr, &tls.Config{ServerName: m.cfg.Host})
	if err != nil {
		return fmt.Errorf("tls dial: %w", err)
	}
	client, err := smtp.NewClient(conn, m.cfg.Host)
	if err != nil {
		return fmt.Errorf("smtp client: %w", err)
	}
	defer client.Close()
	return m.transmit(client, auth, to, raw)
}

func (m *SMTPMailer) transmit(client *smtp.Client, auth smtp.Auth, to string, raw []byte) error {
	if auth != nil {
		if ok, _ := client.Extension("AUTH"); ok {
			if err := client.Auth(auth); err != nil {
				return fmt.Errorf("smtp auth: %w", err)
			}
		}
	}
	if err := client.Mail(m.cfg.FromAddress); err != nil {
		return fmt.Errorf("smtp from: %w", err)
	}
	if err := client.Rcpt(to); err != nil {
		return fmt.Errorf("smtp rcpt: %w", err)
	}
	wc, err := client.Data()
	if err != nil {
		return fmt.Errorf("smtp data: %w", err)
	}
	if _, err := wc.Write(raw); err != nil {
		return fmt.Errorf("smtp write: %w", err)
	}
	if err := wc.Close(); err != nil {
		return fmt.Errorf("smtp close: %w", err)
	}
	return client.Quit()
}

// build assembles an RFC 5322 HTML message.
func (m *SMTPMailer) build(msg Message) []byte {
	from := m.cfg.FromAddress
	if m.cfg.FromName != "" {
		from = fmt.Sprintf("%s <%s>", m.cfg.FromName, m.cfg.FromAddress)
	}
	var b strings.Builder
	fmt.Fprintf(&b, "From: %s\r\n", from)
	fmt.Fprintf(&b, "To: %s\r\n", msg.To)
	fmt.Fprintf(&b, "Subject: %s\r\n", encodeHeader(msg.Subject))
	fmt.Fprintf(&b, "Date: %s\r\n", time.Now().Format(time.RFC1123Z))
	b.WriteString("MIME-Version: 1.0\r\n")
	b.WriteString("Content-Type: text/html; charset=UTF-8\r\n")
	b.WriteString("Content-Transfer-Encoding: 8bit\r\n")
	b.WriteString("\r\n")
	b.WriteString(msg.HTML)
	return []byte(b.String())
}

func (c Config) port() string {
	if c.Port == "" {
		return "587"
	}
	return c.Port
}

// encodeHeader MIME-encodes a header value when it contains non-ASCII runes.
func encodeHeader(s string) string {
	for _, r := range s {
		if r > 127 {
			return "=?UTF-8?B?" + base64.StdEncoding.EncodeToString([]byte(s)) + "?="
		}
	}
	return s
}
