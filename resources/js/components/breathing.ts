/**
 * Interactive breathing exercise (Wim Hof Method style).
 * Three-phase guided breathing: power breaths → retention → recovery hold.
 *
 * Rebuilt with Alpine.js reactivity — all state is declared as top-level
 * reactive properties; computed getters derive everything else; the template
 * binds the UI with x-text / x-show / :data-* / @click throughout.
 */

import type { AlpineMagics } from '@/types/alpine';

type Phase = 'idle' | 'breathing' | 'retention' | 'recovery' | 'complete';

const PHASE_LABEL: Record<Phase, string> = {
  idle: 'Bereit',
  breathing: 'Atmen',
  retention: 'Halten',
  recovery: 'Erholung',
  complete: 'Geschafft',
};

const PICKER_ITEM_WIDTH = 72;

function formatTime(seconds: number): string {
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);

  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

function clamp(value: number, min: number, max: number): number {
  return Math.min(Math.max(value, min), max);
}

export function breathingApp() {
  return {
    // ─── Reactive state ───────────────────────────────────────────────
    phase: 'idle' as Phase,
    round: 0,
    breath: 0,
    timerSeconds: 0,

    // User-adjustable settings (live-synced with picker + steppers)
    settingBreaths: 30,
    settingRounds: 3,
    settingRecovery: 15,

    // ─── Fixed session config ──────────────────────────────────────────
    inhaleMs: 1800,
    exhaleMs: 1800,

    // ─── Scheduling handles ────────────────────────────────────────────
    _timerHandle: null as number | null,
    _breathHandle: null as number | null,
    _rafHandle: null as number | null,
    _retentionStart: 0,
    _pickerCleanup: null as (() => void) | null,

    // ─── Computed getters (Alpine-reactive) ────────────────────────────

    get cycleMs(): number {
      return this.inhaleMs + this.exhaleMs;
    },

    get phaseLabel(): string {
      return PHASE_LABEL[this.phase];
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
      this._pickerCleanup?.();
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
        this._clearScheduled();

        if (this.round >= this.settingRounds) {
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

      // Breath counter — increments once per full inhale + exhale cycle.
      this._breathHandle = window.setInterval(() => {
        if (this.phase !== 'breathing') return;

        this.breath += 1;

        if (this.breath > this.settingBreaths) {
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

      let remaining = this.settingRecovery;

      this.timerSeconds = remaining;

      const tick = (): void => {
        remaining -= 1;
        this.timerSeconds = remaining;

        if (remaining <= 0) {
          if (this.round >= this.settingRounds) {
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
      const pickerEl = $refs['breathsPicker'];
      const trackEl = $refs['breathsTrack'];

      if (!pickerEl || !trackEl) return;

      const min = 10;
      const max = 60;
      const stepSize = 5;
      const values: number[] = [];

      for (let v = min; v <= max; v += stepSize) values.push(v);

      // Build picker item buttons
      trackEl.textContent = '';

      for (const value of values) {
        const item = document.createElement('button');

        item.type = 'button';
        item.className = 'breathing-picker__item';
        item.dataset.value = String(value);
        item.textContent = String(value);
        item.setAttribute('aria-label', `${value} Atemzüge`);
        trackEl.appendChild(item);
      }

      const items = Array.from(
        trackEl.querySelectorAll<HTMLElement>('.breathing-picker__item')
      );

      const indexOfValue = (v: number): number =>
        clamp(Math.round((v - min) / stepSize), 0, values.length - 1);

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
        this.settingBreaths = values[currentIndex] ?? min; // ← reactive update drives template
        highlight(currentIndex);
        applyTransform(animated);
      };

      const onPointerDown = (e: PointerEvent): void => {
        if (this.isActive) return;
        if (e.pointerType === 'mouse' && e.button !== 0) return;

        pointerId = e.pointerId;
        dragStartX = e.clientX;
        dragStartIndex = currentIndex;
        dragged = false;
        trackEl.setPointerCapture(e.pointerId);
        pickerEl.classList.add('is-dragging');
      };

      const onPointerMove = (e: PointerEvent): void => {
        if (pointerId !== e.pointerId) return;

        const delta = e.clientX - dragStartX;

        if (!dragged && Math.abs(delta) > 4) dragged = true;

        dragOffset = delta;
        applyTransform(false);
      };

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

      trackEl.addEventListener('pointerdown', onPointerDown);
      trackEl.addEventListener('pointermove', onPointerMove);
      trackEl.addEventListener('pointerup', onPointerEnd);
      trackEl.addEventListener('pointercancel', onPointerEnd);

      trackEl.addEventListener('click', (e) => {
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

        setIndex(indexOfValue(Number.parseInt(target.dataset.value, 10)), true);
      });

      pickerEl.addEventListener('keydown', (e) => {
        if (this.isActive) return;

        if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
          e.preventDefault();
          setIndex(currentIndex - 1);
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
          e.preventDefault();
          setIndex(currentIndex + 1);
        } else if (e.key === 'Home') {
          e.preventDefault();
          setIndex(0);
        } else if (e.key === 'End') {
          e.preventDefault();
          setIndex(values.length - 1);
        }
      });

      pickerEl.addEventListener(
        'wheel',
        (e) => {
          if (this.isActive) return;
          if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) return;

          e.preventDefault();

          if (e.deltaX > 10) setIndex(currentIndex + 1);
          else if (e.deltaX < -10) setIndex(currentIndex - 1);
        },
        { passive: false }
      );

      // Set initial position without animation
      setIndex(currentIndex, false);

      this._pickerCleanup = (): void => {
        trackEl.removeEventListener('pointerdown', onPointerDown);
        trackEl.removeEventListener('pointermove', onPointerMove);
        trackEl.removeEventListener('pointerup', onPointerEnd);
        trackEl.removeEventListener('pointercancel', onPointerEnd);
      };
    },
  };
}
