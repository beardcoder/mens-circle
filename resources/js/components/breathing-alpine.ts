/**
 * Alpine.js Breathing App Component
 * Interactive Wim Hof style breathing exercise with three phases:
 * breathing -> retention -> recovery
 */

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
  const minutes = Math.floor(seconds / 60);
  const remainder = Math.floor(seconds % 60);
  return `${minutes.toString().padStart(2, '0')}:${remainder.toString().padStart(2, '0')}`;
}

function clamp(value: number, min: number, max: number): number {
  return Math.min(Math.max(value, min), max);
}

export function breathingApp(initialConfig: {
  breaths: number;
  rounds: number;
  recoveryHold: number;
  inhaleMs: number;
  exhaleMs: number;
}) {
  return {
    // Configuration
    config: { ...initialConfig },

    // State
    phase: 'idle' as Phase,
    round: 0,
    breath: 0,
    retentionStart: 0,
    timer: 0,

    // Handles
    timerHandle: null as number | null,
    rafHandle: null as number | null,
    breathHandle: null as number | null,

    // Settings state
    settingsLocked: false,

    // Picker state
    pickerDragging: false,
    pickerOffset: 0,
    pickerPointerId: null as number | null,
    pickerStartX: 0,
    pickerStartIndex: 0,
    pickerCurrentIndex: 0,

    // Computed
    get phaseLabel(): string {
      return PHASE_LABEL[this.phase];
    },

    get counterText(): string {
      if (this.phase === 'idle') {
        return `${this.config.rounds} Runden · ${this.config.breaths} Atemzüge`;
      }
      if (this.phase === 'breathing') {
        return `Atemzug ${this.breath}`;
      }
      if (this.phase === 'retention') {
        return `Halten · ${formatTime(this.timer)}`;
      }
      if (this.phase === 'recovery') {
        return `Halten · ${this.timer}s`;
      }
      return 'Nimm dir einen Moment, spüre nach.';
    },

    get roundText(): string {
      return `${this.round}&nbsp;/&nbsp;${this.config.rounds}`;
    },

    get breathText(): string {
      return `${this.breath}&nbsp;/&nbsp;${this.config.breaths}`;
    },

    get timerText(): string {
      return formatTime(this.timer);
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

    get cycleMs(): number {
      return this.config.inhaleMs + this.config.exhaleMs;
    },

    // Lifecycle
    init(): void {
      this.enterIdle();
      this.setupPicker();
    },

    // Phase transitions
    enterIdle(): void {
      this.clearScheduled();
      this.phase = 'idle';
      this.round = 0;
      this.breath = 0;
      this.timer = 0;
      this.settingsLocked = false;
    },

    finish(): void {
      this.clearScheduled();
      this.phase = 'complete';
    },

    startBreathing(): void {
      this.phase = 'breathing';
      this.round += 1;
      this.breath = 1;
      this.timer = 0;

      const cycleMs = this.cycleMs;
      const startedAt = performance.now();

      // Breath counter
      this.breathHandle = window.setInterval(() => {
        if (this.phase !== 'breathing') return;
        this.breath += 1;
        if (this.breath > this.config.breaths) {
          this.startRetention();
        }
      }, cycleMs);

      // Timer
      const timerTick = (): void => {
        if (this.phase !== 'breathing') return;
        this.timer = Math.floor((performance.now() - startedAt) / 1000);
        this.rafHandle = window.setTimeout(timerTick, 1000) as unknown as number;
      };
      this.rafHandle = window.setTimeout(timerTick, 1000) as unknown as number;
    },

    startRetention(): void {
      this.clearScheduled();
      this.phase = 'retention';
      this.retentionStart = performance.now();
      this.timer = 0;

      const tick = (): void => {
        const elapsed = Math.floor((performance.now() - this.retentionStart) / 1000);
        this.timer = elapsed;
        this.rafHandle = requestAnimationFrame(tick);
      };
      this.rafHandle = requestAnimationFrame(tick);
    },

    startRecovery(): void {
      this.clearScheduled();
      this.phase = 'recovery';
      let remaining = this.config.recoveryHold;
      this.timer = remaining;

      const tick = (): void => {
        remaining -= 1;
        if (remaining <= 0) {
          if (this.round >= this.config.rounds) {
            this.finish();
          } else {
            this.startBreathing();
          }
          return;
        }
        this.timer = remaining;
        this.timerHandle = window.setTimeout(tick, 1000);
      };
      this.timerHandle = window.setTimeout(tick, 1000);
    },

    clearScheduled(): void {
      if (this.timerHandle !== null) {
        window.clearTimeout(this.timerHandle);
        this.timerHandle = null;
      }
      if (this.rafHandle !== null) {
        window.clearTimeout(this.rafHandle);
        cancelAnimationFrame(this.rafHandle);
        this.rafHandle = null;
      }
      if (this.breathHandle !== null) {
        window.clearInterval(this.breathHandle);
        this.breathHandle = null;
      }
    },

    // Button handlers
    handleStart(): void {
      this.readSettings();
      const nextCycle = this.config.inhaleMs + this.config.exhaleMs;
      this.$el.style.setProperty('--breathing-cycle-ms', `${nextCycle}ms`);
      this.round = 0;
      this.breath = 0;
      this.timer = 0;
      this.settingsLocked = true;
      this.startBreathing();
    },

    handleHold(): void {
      if (this.phase === 'retention') {
        this.startRecovery();
      } else if (this.phase === 'recovery') {
        this.clearScheduled();
        if (this.round >= this.config.rounds) {
          this.finish();
        } else {
          this.startBreathing();
        }
      }
    },

    handleReset(): void {
      this.enterIdle();
    },

    handleCircleClick(): void {
      if (this.phase === 'idle' || this.phase === 'complete') {
        this.settingsLocked = true;
        this.handleStart();
      }
    },

    // Settings
    readSettings(): void {
      // Settings are bound via x-model, so they're already in config
    },

    stepSetting(key: 'rounds' | 'recoveryHold', delta: number, min: number, max: number): void {
      if (this.settingsLocked) return;
      const current = this.config[key];
      const next = clamp(current + delta, min, max);
      if (next !== current) {
        this.config[key] = next;
      }
    },

    // Picker (breaths setting)
    setupPicker(): void {
      this.$nextTick(() => {
        const track = this.$refs.pickerTrack as HTMLElement;
        if (!track) return;

        const min = 10;
        const max = 60;
        const step = 5;
        const values: number[] = [];
        for (let v = min; v <= max; v += step) values.push(v);

        track.innerHTML = '';
        values.forEach((value) => {
          const item = document.createElement('button');
          item.type = 'button';
          item.className = 'breathing-picker__item';
          item.dataset.value = String(value);
          item.textContent = String(value);
          item.setAttribute('aria-label', `${value} Atemzüge`);
          track.appendChild(item);
        });

        const initialIndex = values.indexOf(this.config.breaths);
        this.pickerCurrentIndex = initialIndex >= 0 ? initialIndex : Math.floor(values.length / 2);
        this.applyPickerTransform(false);
        this.highlightPickerItem();
      });
    },

    handlePickerPointerDown(event: PointerEvent): void {
      if (this.settingsLocked) return;
      if (event.pointerType === 'mouse' && event.button !== 0) return;

      this.pickerPointerId = event.pointerId;
      this.pickerStartX = event.clientX;
      this.pickerStartIndex = this.pickerCurrentIndex;
      this.pickerDragging = false;

      const track = this.$refs.pickerTrack as HTMLElement;
      track.setPointerCapture(event.pointerId);
    },

    handlePickerPointerMove(event: PointerEvent): void {
      if (this.pickerPointerId !== event.pointerId) return;

      const delta = event.clientX - this.pickerStartX;
      if (!this.pickerDragging && Math.abs(delta) > 4) {
        this.pickerDragging = true;
      }
      this.pickerOffset = delta;
      this.applyPickerTransform(false);
    },

    handlePickerPointerEnd(event: PointerEvent): void {
      if (this.pickerPointerId !== event.pointerId) return;

      const delta = this.pickerOffset;
      const indexDelta = Math.round(-delta / PICKER_ITEM_WIDTH);

      this.pickerPointerId = null;
      this.pickerOffset = 0;

      if (this.pickerDragging) {
        this.setPickerIndex(this.pickerStartIndex + indexDelta, true);
      } else {
        this.applyPickerTransform(true);
      }
    },

    handlePickerClick(event: MouseEvent): void {
      if (this.pickerDragging) {
        event.preventDefault();
        event.stopPropagation();
        this.pickerDragging = false;
        return;
      }

      const target = (event.target as HTMLElement).closest<HTMLElement>('.breathing-picker__item');
      if (!target?.dataset.value) return;

      const value = Number.parseInt(target.dataset.value, 10);
      const min = 10;
      const step = 5;
      const index = (value - min) / step;
      this.setPickerIndex(index, true);
    },

    handlePickerKeydown(event: KeyboardEvent): void {
      if (this.settingsLocked) return;

      if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
        event.preventDefault();
        this.setPickerIndex(this.pickerCurrentIndex - 1, true);
      } else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
        event.preventDefault();
        this.setPickerIndex(this.pickerCurrentIndex + 1, true);
      } else if (event.key === 'Home') {
        event.preventDefault();
        this.setPickerIndex(0, true);
      } else if (event.key === 'End') {
        event.preventDefault();
        const max = Math.floor((60 - 10) / 5);
        this.setPickerIndex(max, true);
      }
    },

    handlePickerWheel(event: WheelEvent): void {
      if (this.settingsLocked) return;
      if (Math.abs(event.deltaX) < Math.abs(event.deltaY)) return;
      event.preventDefault();
      if (event.deltaX > 10) {
        this.setPickerIndex(this.pickerCurrentIndex + 1, true);
      } else if (event.deltaX < -10) {
        this.setPickerIndex(this.pickerCurrentIndex - 1, true);
      }
    },

    setPickerIndex(index: number, animated: boolean): void {
      const min = 10;
      const step = 5;
      const max = Math.floor((60 - min) / step);

      this.pickerCurrentIndex = clamp(index, 0, max);
      this.config.breaths = min + this.pickerCurrentIndex * step;
      this.applyPickerTransform(animated);
      this.highlightPickerItem();
    },

    applyPickerTransform(animated: boolean): void {
      const track = this.$refs.pickerTrack as HTMLElement;
      if (!track) return;

      track.style.transition = animated
        ? 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)'
        : 'none';
      const base = -this.pickerCurrentIndex * PICKER_ITEM_WIDTH;
      track.style.transform = `translate3d(${base + this.pickerOffset}px, 0, 0)`;
    },

    highlightPickerItem(): void {
      const track = this.$refs.pickerTrack as HTMLElement;
      if (!track) return;

      const items = track.querySelectorAll<HTMLElement>('.breathing-picker__item');
      items.forEach((item, i) => {
        item.classList.toggle('is-active', i === this.pickerCurrentIndex);
        item.tabIndex = i === this.pickerCurrentIndex ? 0 : -1;
      });
    },
  };
}
