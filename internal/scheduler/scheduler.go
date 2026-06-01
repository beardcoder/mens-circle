// Package scheduler runs periodic background jobs, currently event reminders.
package scheduler

import (
	"context"
	"log/slog"
	"time"

	"github.com/beardcoder/mens-circle/internal/notify"
	"github.com/beardcoder/mens-circle/internal/repository"
)

// Scheduler periodically dispatches event reminder emails.
type Scheduler struct {
	repos    *repository.Repositories
	notifier *notify.Service
	logger   *slog.Logger
	interval time.Duration
}

// New creates a scheduler that checks for due reminders every interval.
func New(repos *repository.Repositories, notifier *notify.Service, logger *slog.Logger, interval time.Duration) *Scheduler {
	if interval <= 0 {
		interval = time.Hour
	}
	return &Scheduler{repos: repos, notifier: notifier, logger: logger, interval: interval}
}

// Start runs the reminder loop until the context is cancelled.
func (s *Scheduler) Start(ctx context.Context) {
	ticker := time.NewTicker(s.interval)
	defer ticker.Stop()

	s.runReminders() // run once at startup
	for {
		select {
		case <-ctx.Done():
			return
		case <-ticker.C:
			s.runReminders()
		}
	}
}

// runReminders sends reminders for events happening today or tomorrow that have
// not yet been reminded.
func (s *Scheduler) runReminders() {
	now := time.Now()
	from := time.Date(now.Year(), now.Month(), now.Day(), 0, 0, 0, 0, now.Location())
	to := from.AddDate(0, 0, 2).Add(-time.Second) // end of tomorrow

	due, err := s.repos.Registrations.DueReminders(from, to)
	if err != nil {
		s.logger.Error("scheduler: load due reminders", "err", err)
		return
	}
	if len(due) == 0 {
		return
	}

	sent := 0
	for _, reg := range due {
		if reg.Participant == nil || reg.Event == nil {
			continue
		}
		isToday := sameDay(reg.Event.EventDate, now)
		if err := s.notifier.EventReminder(reg.Participant, reg.Event, isToday); err != nil {
			s.logger.Error("scheduler: send reminder", "registration", reg.ID, "err", err)
			continue
		}
		hasPhone := reg.Participant.Phone != nil && *reg.Participant.Phone != ""
		if err := s.repos.Registrations.MarkReminderSent(reg.ID, hasPhone); err != nil {
			s.logger.Error("scheduler: mark reminder sent", "registration", reg.ID, "err", err)
		}
		sent++
	}
	s.logger.Info("scheduler: reminders dispatched", "count", sent)
}

func sameDay(a, b time.Time) bool {
	return a.Year() == b.Year() && a.YearDay() == b.YearDay()
}
