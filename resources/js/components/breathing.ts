/**
 * Interactive breathing exercise (Wim Hof Method style).
 * Three-phase guided breathing: power breaths → retention → recovery hold.
 *
 * State machine:
 *   idle → breathing → retention → recovery → [next breathing | complete]
 *
 * On session start the current UI settings are snapshotted into `_session`
 * so that adjusting the picker / steppers mid-session has no effect on the
 * running round.  All phase-transition closures read from `_session`.
 *
 * Template binds via x-text / x-show / :data-* / @click — no querySelector.
 */

import { clamp } from '@/utils/helpers';
import type { AlpineMagics } from '@/types/alpine';

type Phase = 'idle' | 'breathing' | 'retention' | 'recovery' | 'complete';

/** Settings frozen at the moment a session begins. */
type SessionConfig = {
  breaths: number;
  rounds: number;
  recoveryHold: number;
};

const PHASE_LABEL: Record<Phase, string> = {
  idle: 'Bereit',
  breathing: 'Atmen',
  retention: 'Halten',
  recovery: 'Erholung',
  complete: 'Geschafft',
};

const PICKER_ITEM_WIDTH = 72;
const PICKER_MIN = 10;
const PICKER_MAX = 60;
const PICKER_STEP = 5;
const DRAG_THRESHOLD_PX = 4;

function formatTime(seconds: number): string {
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);

  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

export function breathingApp() {
  return {
    // ─── Reactive state ───────────────────────────────────────────────
    phase: 'idle' as Phase,
    round: 0,
    breath: 0,
    timerSeconds: 0,

    // User-adjustable settings (live-synced with picker + steppers).
    // Changing these while a session is active has no effect on the
    // running round because transitions read from `_session` instead.
    settingBreaths: 35,
    settingRounds: 3,
    settingRecovery: 15,

    // ─── Session snapshot ──────────────────────────────────────────────
    /** Frozen copy of settings taken at session start. Null when idle. */
    _session: null as SessionConfig | null,

    // ─── Fixed per-breath timing ───────────────────────────────────────
    inhaleMs: 1800,
    exhaleMs: 1800,

    // ─── Scheduling handles ────────────────────────────────────────────
    _timerHandle: null as number | null,
    _breathHandle: null as number | null,
    _rafHandle: null as number | null,
    _retentionStart: 0,
    _pickerController: new AbortController(),

    // ─── Computed getters (Alpine-reactive) ────────────────────────────

    get cycleMs(): number {
      return this.inhaleMs + this.exhaleMs;
    },

    get phaseLabel(): string {
      return PHASE_LABEL[this.phase];
    },

    /**
     * Session-frozen round count — drives the meta row.
     * Falls back to the live setting when idle / complete.
     */
    get sessionRounds(): number {
      return this._session?.rounds ?? this.settingRounds;
    },

    /**
     * Session-frozen breath count — drives the meta row.
     * Falls back to the live setting when idle / complete.
     */
    get sessionBreaths(): number {
      return this._session?.breaths ?? this.settingBreaths;
    },

    /**
     * Sub-label inside the circle. Re-evaluates automatically when any
     * of phase / breath / timerSeconds / settingBreaths / settingRounds changes.
     */
    get counter(): string {
      switch (this.phase) {
        case 'idle':
          return `${this.settingRounds} Runden · ${this.settingBreaths} Atemzüge`;
        case 'breathing':
          return `Atemzug ${this.breath}`;
        case 'retention':
          return `Halten · ${formatTime(this.timerSeconds)}`;
        case 'recovery':
          return `Halten · ${this.timerSeconds}s`;
        case 'complete':
          return 'Nimm dir einen Moment, spüre nach.';
      }
    },

    get formattedTimer(): string {
      return formatTime(this.timerSeconds);
    },

    get showStartButton(): boolean {
      return this.phase === 'idle' || this.phase === 'complete';
    },

    get showHoldButton(): boolean {
      return this.phase === 'retention' || this.phase === 'recovery';
    },

    get holdButtonText(): string {
      return this.phase === 'recovery' ? 'Weiteratmen' : 'Atem freigeben';
    },

    get startButtonLabel(): string {
      return this.phase === 'complete' ? 'Erneut starten' : 'Atemübung starten';
    },

    /** True while a session is running — locks all settings controls. */
    get isActive(): boolean {
      return (
        this.phase === 'breathing' ||
        this.phase === 'retention' ||
        this.phase === 'recovery'
      );
    },

    /**
     * data-motion value bound to the circle element.
     * Returns null during idle/complete so Alpine removes the attribute.
     */
    get circleMotion(): string | null {
      if (this.phase === 'breathing') return 'wave';
      if (this.phase === 'retention') return 'hold-high';
      if (this.phase === 'recovery') return 'hold-low';

      return null;
    },

    // ─── Lifecycle ─────────────────────────────────────────────────────

    init(): void {
      const { $el, $refs } = this as unknown as AlpineMagics;

      $el.style.setProperty('--breathing-cycle-ms', `${this.cycleMs}ms`);
      this._setupPicker($refs);
    },

    destroy(): void {
      this._clearScheduled();
      this._pickerController.abort();
    },

    // ─── Public actions (bound in the template) ────────────────────────

    handleCircleClick(): void {
      if (this.phase === 'idle' || this.phase === 'complete') {
        this._beginSession();
      }
    },

    handleStart(): void {
      this._beginSession();
    },

    handleHold(): void {
      if (this.phase === 'retention') {
        this._startRecovery();

        return;
      }

      if (this.phase === 'recovery') {
        // Both _finish() and _startBreathing() clear scheduled timers at
        // their own start, so no need to call _clearScheduled() here.
        if (this.round >= (this._session?.rounds ?? this.settingRounds)) {
          this._finish();

          return;
        }

        this._startBreathing();
      }
    },

    handleReset(): void {
      this._enterIdle();
    },

    adjustRounds(delta: number): void {
      if (this.isActive) return;

      this.settingRounds = clamp(this.settingRounds + delta, 1, 6);
    },

    adjustRecovery(delta: number): void {
      if (this.isActive) return;

      this.settingRecovery = clamp(this.settingRecovery + delta, 5, 30);
    },

    // ─── Phase transitions ─────────────────────────────────────────────

    _beginSession(): void {
      // Snapshot current UI settings so mid-session picker/stepper changes
      // cannot affect the running round.
      this._session = {
        breaths: this.settingBreaths,
        rounds: this.settingRounds,
        recoveryHold: this.settingRecovery,
      };
      this.round = 0;
      this.breath = 0;
      this.timerSeconds = 0;
      this._startBreathing();
    },

    _enterIdle(): void {
      this._clearScheduled();
      this.phase = 'idle';
      this.round = 0;
      this.breath = 0;
      this.timerSeconds = 0;
      this._session = null;
    },

    _finish(): void {
      this._clearScheduled();
      this.phase = 'complete';
    },

    _startBreathing(): void {
      this._clearScheduled();
      this.phase = 'breathing';
      this.round += 1;
      this.breath = 1;
      this.timerSeconds = 0;

      const startedAt = performance.now();
      // Capture the session breath limit once — the interval closure must not
      // read the live `settingBreaths` property, which the user could change
      // while settings are unlocked between rounds.
      const breathLimit = this._session?.breaths ?? this.settingBreaths;

      // Breath counter — increments once per full inhale + exhale cycle.
      this._breathHandle = window.setInterval(() => {
        if (this.phase !== 'breathing') return;

        this.breath += 1;

        if (this.breath > breathLimit) {
          this._startRetention();
        }
      }, this.cycleMs);

      // Elapsed timer — chain-scheduled every second using performance.now()
      // so it stays accurate regardless of setTimeout drift.
      const timerTick = (): void => {
        if (this.phase !== 'breathing') return;

        this.timerSeconds = Math.floor((performance.now() - startedAt) / 1000);
        this._timerHandle = window.setTimeout(timerTick, 1000);
      };

      this._timerHandle = window.setTimeout(timerTick, 1000);
    },

    _startRetention(): void {
      this._clearScheduled();
      this.phase = 'retention';
      this._retentionStart = performance.now();
      this.timerSeconds = 0;

      // RAF loop — runs every frame but only triggers a reactive write when
      // the elapsed second actually changes, keeping updates to once per second.
      let lastSecond = -1;

      const tick = (): void => {
        if (this.phase !== 'retention') return;

        const elapsed = Math.floor(
          (performance.now() - this._retentionStart) / 1000
        );

        if (elapsed !== lastSecond) {
          lastSecond = elapsed;
          this.timerSeconds = elapsed;
        }

        this._rafHandle = requestAnimationFrame(tick);
      };

      this._rafHandle = requestAnimationFrame(tick);
    },

    _startRecovery(): void {
      this._clearScheduled();
      this.phase = 'recovery';

      // Read from the session snapshot so the recovery duration cannot change
      // if the user touches the stepper between rounds.
      let remaining = this._session?.recoveryHold ?? this.settingRecovery;

      this.timerSeconds = remaining;

      const tick = (): void => {
        remaining -= 1;
        this.timerSeconds = remaining;

        if (remaining <= 0) {
          if (this.round >= (this._session?.rounds ?? this.settingRounds)) {
            this._finish();

            return;
          }

          this._startBreathing();

          return;
        }

        this._timerHandle = window.setTimeout(tick, 1000);
      };

      this._timerHandle = window.setTimeout(tick, 1000);
    },

    _clearScheduled(): void {
      if (this._breathHandle !== null) {
        window.clearInterval(this._breathHandle);
        this._breathHandle = null;
      }

      if (this._timerHandle !== null) {
        window.clearTimeout(this._timerHandle);
        this._timerHandle = null;
      }

      if (this._rafHandle !== null) {
        cancelAnimationFrame(this._rafHandle);
        this._rafHandle = null;
      }
    },

    // ─── iOS-style swipe picker for breaths ────────────────────────────

    _setupPicker($refs: Record<string, HTMLElement>): void {
      const pickerEl = $refs.breathsPicker;
      const trackEl = $refs.breathsTrack;

      if (!pickerEl || !trackEl) return;

      const values: number[] = [];

      for (let v = PICKER_MIN; v <= PICKER_MAX; v += PICKER_STEP) {
        values.push(v);
      }

      trackEl.replaceChildren(
        ...values.map((value) => {
          const item = document.createElement('button');

          item.type = 'button';
          item.className = 'breathing-picker__item';
          item.dataset.value = String(value);
          item.textContent = String(value);
          item.setAttribute('aria-label', `${value} Atemzüge`);

          return item;
        })
      );

      const items = Array.from(
        trackEl.querySelectorAll<HTMLElement>('.breathing-picker__item')
      );

      const indexOfValue = (v: number): number =>
        clamp(Math.round((v - PICKER_MIN) / PICKER_STEP), 0, values.length - 1);

      let currentIndex = indexOfValue(this.settingBreaths);
      let dragOffset = 0;
      let pointerId: number | null = null;
      let dragStartX = 0;
      let dragStartIndex = 0;
      let dragged = false;

      const applyTransform = (animated: boolean): void => {
        trackEl.style.transition = animated
          ? 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)'
          : 'none';
        trackEl.style.transform = `translate3d(${-currentIndex * PICKER_ITEM_WIDTH + dragOffset}px, 0, 0)`;
      };

      const highlight = (index: number): void => {
        items.forEach((item, i) => {
          item.classList.toggle('is-active', i === index);
          item.tabIndex = i === index ? 0 : -1;
        });
      };

      const setIndex = (index: number, animated = true): void => {
        currentIndex = clamp(index, 0, values.length - 1);
        this.settingBreaths = values[currentIndex] as number;
        highlight(currentIndex);
        applyTransform(animated);
      };

      const { signal } = this._pickerController;

      trackEl.addEventListener(
        'pointerdown',
        (e: PointerEvent) => {
          if (this.isActive) return;
          if (e.pointerType === 'mouse' && e.button !== 0) return;

          pointerId = e.pointerId;
          dragStartX = e.clientX;
          dragStartIndex = currentIndex;
          dragged = false;
          trackEl.setPointerCapture(e.pointerId);
          pickerEl.classList.add('is-dragging');
        },
        { signal }
      );

      trackEl.addEventListener(
        'pointermove',
        (e: PointerEvent) => {
          if (pointerId !== e.pointerId) return;

          const delta = e.clientX - dragStartX;

          if (!dragged && Math.abs(delta) > DRAG_THRESHOLD_PX) dragged = true;

          dragOffset = delta;
          applyTransform(false);
        },
        { signal }
      );

      const onPointerEnd = (e: PointerEvent): void => {
        if (pointerId !== e.pointerId) return;

        const indexDelta = Math.round(-dragOffset / PICKER_ITEM_WIDTH);

        pointerId = null;
        dragOffset = 0;
        pickerEl.classList.remove('is-dragging');

        if (dragged) {
          setIndex(dragStartIndex + indexDelta, true);
        } else {
          applyTransform(true);
        }
      };

      trackEl.addEventListener('pointerup', onPointerEnd, { signal });
      trackEl.addEventListener('pointercancel', onPointerEnd, { signal });

      trackEl.addEventListener(
        'click',
        (e: MouseEvent) => {
          if (this.isActive) {
            e.preventDefault();

            return;
          }

          if (dragged) {
            e.preventDefault();
            e.stopPropagation();
            dragged = false;

            return;
          }

          const target = (e.target as HTMLElement).closest<HTMLElement>(
            '.breathing-picker__item'
          );

          if (!target?.dataset.value) return;

          setIndex(
            indexOfValue(Number.parseInt(target.dataset.value, 10)),
            true
          );
        },
        { signal }
      );

      pickerEl.addEventListener(
        'keydown',
        (e: KeyboardEvent) => {
          if (this.isActive) return;

          switch (e.key) {
            case 'ArrowLeft':
            case 'ArrowDown':
              e.preventDefault();
              setIndex(currentIndex - 1);
              break;
            case 'ArrowRight':
            case 'ArrowUp':
              e.preventDefault();
              setIndex(currentIndex + 1);
              break;
            case 'Home':
              e.preventDefault();
              setIndex(0);
              break;
            case 'End':
              e.preventDefault();
              setIndex(values.length - 1);
              break;
          }
        },
        { signal }
      );

      pickerEl.addEventListener(
        'wheel',
        (e: WheelEvent) => {
          if (this.isActive) return;
          if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) return;

          e.preventDefault();

          if (e.deltaX > 10) setIndex(currentIndex + 1);
          else if (e.deltaX < -10) setIndex(currentIndex - 1);
        },
        { passive: false, signal }
      );

      setIndex(currentIndex, false);
    },
  };
}
