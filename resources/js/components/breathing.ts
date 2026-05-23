/**
 * Interactive breathing exercise (Wim Hof Method style).
 *
 * Three-phase guided breathing: power breaths → retention → recovery hold.
 * State machine: idle → breathing → retention → recovery → [next | complete]
 *
 * The DOM ships fully rendered for the idle state; this class subscribes
 * to user input, runs the state machine, and updates DOM via `render()`.
 * Every binding uses `data-ref="…"` or `data-action="…"` in the template
 * so there are no inline directives.
 *
 * Settings are snapshotted into `session` when a session starts so
 * adjusting the picker / steppers mid-session has no effect on the
 * running round.
 */

import { clamp } from '@/utils/helpers';
import { mountAll, ReactiveHost } from '@/lib/reactive-host';

type Phase = 'idle' | 'breathing' | 'retention' | 'recovery' | 'complete';

interface SessionConfig {
  breaths: number;
  rounds: number;
  recoveryHold: number;
}

interface BreathingState {
  phase: Phase;
  round: number;
  breath: number;
  timerSeconds: number;
  settingBreaths: number;
  settingRounds: number;
  settingRecovery: number;
  session: SessionConfig | null;
}

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
const INHALE_MS = 1800;
const EXHALE_MS = 1800;
const CYCLE_MS = INHALE_MS + EXHALE_MS;

function formatTime(seconds: number): string {
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);

  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

class BreathingApp extends ReactiveHost {
  private state: BreathingState = {
    phase: 'idle',
    round: 0,
    breath: 0,
    timerSeconds: 0,
    settingBreaths: 35,
    settingRounds: 3,
    settingRecovery: 15,
    session: null,
  };

  // Captured DOM references
  private $circle!: HTMLElement;
  private $phaseLabel!: HTMLElement;
  private $counter!: HTMLElement;
  private $metaRound!: HTMLElement;
  private $metaBreath!: HTMLElement;
  private $metaTimer!: HTMLElement;
  private $startBtn!: HTMLButtonElement;
  private $holdBtn!: HTMLButtonElement;
  private $picker!: HTMLElement;
  private $pickerTrack!: HTMLElement;
  private $roundsValue!: HTMLElement;
  private $roundsMinusBtn!: HTMLButtonElement;
  private $roundsPlusBtn!: HTMLButtonElement;
  private $recoveryValue!: HTMLElement;
  private $recoveryMinusBtn!: HTMLButtonElement;
  private $recoveryPlusBtn!: HTMLButtonElement;

  // Picker internals
  private pickerValues: number[] = [];
  private pickerIndex = 0;

  // Scheduling
  private timerHandle: number | null = null;
  private breathHandle: number | null = null;
  private rafHandle: number | null = null;
  private retentionStartMs = 0;

  protected setup(): void {
    this.root.style.setProperty('--breathing-cycle-ms', `${CYCLE_MS}ms`);

    this.captureRefs();
    this.setupPicker();
    this.bindActions();
  }

  protected render(): void {
    const {
      phase,
      round,
      breath,
      timerSeconds,
      settingBreaths,
      settingRounds,
      settingRecovery,
      session,
    } = this.state;

    const sessionRounds = session?.rounds ?? settingRounds;
    const sessionBreaths = session?.breaths ?? settingBreaths;

    this.root.dataset.phase = phase;

    const motion = this.circleMotion(phase);

    if (motion) this.$circle.dataset.motion = motion;
    else delete this.$circle.dataset.motion;

    this.$phaseLabel.textContent = PHASE_LABEL[phase];
    this.$counter.textContent = this.counterText();

    this.$metaRound.textContent = `${round} / ${sessionRounds}`;
    this.$metaBreath.textContent = `${breath} / ${sessionBreaths}`;
    this.$metaTimer.textContent = formatTime(timerSeconds);

    const showStart = phase === 'idle' || phase === 'complete';
    const showHold = phase === 'retention' || phase === 'recovery';

    this.$startBtn.hidden = !showStart;
    this.$holdBtn.hidden = !showHold;

    const startLabel =
      phase === 'complete' ? 'Erneut starten' : 'Atemübung starten';

    this.$startBtn.setAttribute('aria-label', startLabel);
    this.$startBtn.title = startLabel;

    this.$holdBtn.textContent =
      phase === 'recovery' ? 'Weiteratmen' : 'Atem freigeben';

    const isActive =
      phase === 'breathing' || phase === 'retention' || phase === 'recovery';

    this.$picker.setAttribute('aria-valuenow', String(settingBreaths));

    if (isActive) this.$picker.setAttribute('aria-disabled', 'true');
    else this.$picker.removeAttribute('aria-disabled');

    this.$roundsValue.textContent = String(settingRounds);
    this.$roundsValue.setAttribute('aria-valuenow', String(settingRounds));
    this.$roundsMinusBtn.disabled = isActive || settingRounds <= 1;
    this.$roundsPlusBtn.disabled = isActive || settingRounds >= 6;

    this.$recoveryValue.textContent = String(settingRecovery);
    this.$recoveryValue.setAttribute('aria-valuenow', String(settingRecovery));
    this.$recoveryMinusBtn.disabled = isActive || settingRecovery <= 5;
    this.$recoveryPlusBtn.disabled = isActive || settingRecovery >= 30;
  }

  protected teardown(): void {
    this.clearScheduled();
  }

  // ─── DOM setup ─────────────────────────────────────────────────────────

  private captureRefs(): void {
    this.$circle = this.requireRef('circle');
    this.$phaseLabel = this.requireRef('phase-label');
    this.$counter = this.requireRef('counter');
    this.$metaRound = this.requireRef('meta-round');
    this.$metaBreath = this.requireRef('meta-breath');
    this.$metaTimer = this.requireRef('meta-timer');
    this.$startBtn = this.requireRef('start');
    this.$holdBtn = this.requireRef('hold');
    this.$picker = this.requireRef('picker');
    this.$pickerTrack = this.requireRef('picker-track');
    this.$roundsValue = this.requireRef('rounds-value');
    this.$roundsMinusBtn = this.requireRef('rounds-minus');
    this.$roundsPlusBtn = this.requireRef('rounds-plus');
    this.$recoveryValue = this.requireRef('recovery-value');
    this.$recoveryMinusBtn = this.requireRef('recovery-minus');
    this.$recoveryPlusBtn = this.requireRef('recovery-plus');
  }

  private requireRef<T extends HTMLElement>(name: string): T {
    const el = this.query<T>(`[data-ref="${name}"]`);

    if (!el) {
      throw new Error(`[breathing] missing [data-ref="${name}"]`);
    }

    return el;
  }

  private bindActions(): void {
    this.on(this.$circle, 'click', () => this.handleCircleClick());
    this.on(this.$startBtn, 'click', () => this.beginSession());
    this.on(this.$holdBtn, 'click', () => this.handleHold());
    this.on(this.requireRef<HTMLButtonElement>('reset'), 'click', () =>
      this.enterIdle()
    );
    this.on(this.$roundsMinusBtn, 'click', () => this.adjustRounds(-1));
    this.on(this.$roundsPlusBtn, 'click', () => this.adjustRounds(1));
    this.on(this.$recoveryMinusBtn, 'click', () => this.adjustRecovery(-1));
    this.on(this.$recoveryPlusBtn, 'click', () => this.adjustRecovery(1));
  }

  // ─── State helpers ─────────────────────────────────────────────────────

  private get isActive(): boolean {
    const { phase } = this.state;

    return (
      phase === 'breathing' || phase === 'retention' || phase === 'recovery'
    );
  }

  private circleMotion(phase: Phase): string | null {
    if (phase === 'breathing') return 'wave';
    if (phase === 'retention') return 'hold-high';
    if (phase === 'recovery') return 'hold-low';

    return null;
  }

  private counterText(): string {
    const { phase, breath, timerSeconds, settingBreaths, settingRounds } =
      this.state;

    switch (phase) {
      case 'idle':
        return `${settingRounds} Runden · ${settingBreaths} Atemzüge`;
      case 'breathing':
        return `Atemzug ${breath}`;
      case 'retention':
        return `Halten · ${formatTime(timerSeconds)}`;
      case 'recovery':
        return `Halten · ${timerSeconds}s`;
      case 'complete':
        return 'Nimm dir einen Moment, spüre nach.';
    }
  }

  // ─── User actions ──────────────────────────────────────────────────────

  private handleCircleClick(): void {
    if (this.state.phase === 'idle' || this.state.phase === 'complete') {
      this.beginSession();
    }
  }

  private handleHold(): void {
    if (this.state.phase === 'retention') {
      this.startRecovery();

      return;
    }

    if (this.state.phase === 'recovery') {
      const sessionRounds =
        this.state.session?.rounds ?? this.state.settingRounds;

      if (this.state.round >= sessionRounds) {
        this.finish();

        return;
      }

      this.startBreathing();
    }
  }

  private adjustRounds(delta: number): void {
    if (this.isActive) return;

    this.state.settingRounds = clamp(this.state.settingRounds + delta, 1, 6);
    this.render();
  }

  private adjustRecovery(delta: number): void {
    if (this.isActive) return;

    this.state.settingRecovery = clamp(
      this.state.settingRecovery + delta,
      5,
      30
    );
    this.render();
  }

  // ─── Phase transitions ─────────────────────────────────────────────────

  private beginSession(): void {
    this.state.session = {
      breaths: this.state.settingBreaths,
      rounds: this.state.settingRounds,
      recoveryHold: this.state.settingRecovery,
    };
    this.state.round = 0;
    this.state.breath = 0;
    this.state.timerSeconds = 0;
    this.startBreathing();
  }

  private enterIdle(): void {
    this.clearScheduled();
    this.state.phase = 'idle';
    this.state.round = 0;
    this.state.breath = 0;
    this.state.timerSeconds = 0;
    this.state.session = null;
    this.render();
  }

  private finish(): void {
    this.clearScheduled();
    this.state.phase = 'complete';
    this.render();
  }

  private startBreathing(): void {
    this.clearScheduled();
    this.state.phase = 'breathing';
    this.state.round += 1;
    this.state.breath = 1;
    this.state.timerSeconds = 0;
    this.render();

    const startedAt = performance.now();
    const breathLimit =
      this.state.session?.breaths ?? this.state.settingBreaths;

    this.breathHandle = window.setInterval(() => {
      if (this.state.phase !== 'breathing') return;

      this.state.breath += 1;

      if (this.state.breath > breathLimit) {
        this.startRetention();

        return;
      }

      this.render();
    }, CYCLE_MS);

    const tick = (): void => {
      if (this.state.phase !== 'breathing') return;

      this.state.timerSeconds = Math.floor(
        (performance.now() - startedAt) / 1000
      );
      this.render();
      this.timerHandle = window.setTimeout(tick, 1000);
    };

    this.timerHandle = window.setTimeout(tick, 1000);
  }

  private startRetention(): void {
    this.clearScheduled();
    this.state.phase = 'retention';
    this.retentionStartMs = performance.now();
    this.state.timerSeconds = 0;
    this.render();

    let lastSecond = -1;

    const loop = (): void => {
      if (this.state.phase !== 'retention') return;

      const elapsed = Math.floor(
        (performance.now() - this.retentionStartMs) / 1000
      );

      if (elapsed !== lastSecond) {
        lastSecond = elapsed;
        this.state.timerSeconds = elapsed;
        this.render();
      }

      this.rafHandle = requestAnimationFrame(loop);
    };

    this.rafHandle = requestAnimationFrame(loop);
  }

  private startRecovery(): void {
    this.clearScheduled();
    this.state.phase = 'recovery';

    let remaining =
      this.state.session?.recoveryHold ?? this.state.settingRecovery;

    this.state.timerSeconds = remaining;
    this.render();

    const tick = (): void => {
      remaining -= 1;
      this.state.timerSeconds = remaining;
      this.render();

      if (remaining <= 0) {
        const sessionRounds =
          this.state.session?.rounds ?? this.state.settingRounds;

        if (this.state.round >= sessionRounds) {
          this.finish();

          return;
        }

        this.startBreathing();

        return;
      }

      this.timerHandle = window.setTimeout(tick, 1000);
    };

    this.timerHandle = window.setTimeout(tick, 1000);
  }

  private clearScheduled(): void {
    if (this.breathHandle !== null) {
      window.clearInterval(this.breathHandle);
      this.breathHandle = null;
    }

    if (this.timerHandle !== null) {
      window.clearTimeout(this.timerHandle);
      this.timerHandle = null;
    }

    if (this.rafHandle !== null) {
      cancelAnimationFrame(this.rafHandle);
      this.rafHandle = null;
    }
  }

  // ─── iOS-style swipe picker ────────────────────────────────────────────

  private setupPicker(): void {
    for (let v = PICKER_MIN; v <= PICKER_MAX; v += PICKER_STEP) {
      this.pickerValues.push(v);
    }

    this.$pickerTrack.replaceChildren(
      ...this.pickerValues.map((value) => {
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
      this.$pickerTrack.querySelectorAll<HTMLElement>('.breathing-picker__item')
    );

    const indexOfValue = (v: number): number =>
      clamp(
        Math.round((v - PICKER_MIN) / PICKER_STEP),
        0,
        this.pickerValues.length - 1
      );

    this.pickerIndex = indexOfValue(this.state.settingBreaths);
    let dragOffset = 0;
    let pointerId: number | null = null;
    let dragStartX = 0;
    let dragStartIndex = 0;
    let dragged = false;

    const applyTransform = (animated: boolean): void => {
      this.$pickerTrack.style.transition = animated
        ? 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)'
        : 'none';
      this.$pickerTrack.style.transform = `translate3d(${
        -this.pickerIndex * PICKER_ITEM_WIDTH + dragOffset
      }px, 0, 0)`;
    };

    const highlight = (index: number): void => {
      items.forEach((item, i) => {
        item.classList.toggle('is-active', i === index);
        item.tabIndex = i === index ? 0 : -1;
      });
    };

    const setIndex = (index: number, animated = true): void => {
      this.pickerIndex = clamp(index, 0, this.pickerValues.length - 1);
      this.state.settingBreaths = this.pickerValues[this.pickerIndex] as number;
      highlight(this.pickerIndex);
      applyTransform(animated);
      this.render();
    };

    this.on(this.$pickerTrack, 'pointerdown', (e) => {
      if (this.isActive) return;
      if (e.pointerType === 'mouse' && e.button !== 0) return;

      pointerId = e.pointerId;
      dragStartX = e.clientX;
      dragStartIndex = this.pickerIndex;
      dragged = false;
      this.$pickerTrack.setPointerCapture(e.pointerId);
      this.$picker.classList.add('is-dragging');
    });

    this.on(this.$pickerTrack, 'pointermove', (e) => {
      if (pointerId !== e.pointerId) return;

      const delta = e.clientX - dragStartX;

      if (!dragged && Math.abs(delta) > DRAG_THRESHOLD_PX) dragged = true;

      dragOffset = delta;
      applyTransform(false);
    });

    const onPointerEnd = (e: PointerEvent): void => {
      if (pointerId !== e.pointerId) return;

      const indexDelta = Math.round(-dragOffset / PICKER_ITEM_WIDTH);

      pointerId = null;
      dragOffset = 0;
      this.$picker.classList.remove('is-dragging');

      if (dragged) setIndex(dragStartIndex + indexDelta, true);
      else applyTransform(true);
    };

    this.on(this.$pickerTrack, 'pointerup', onPointerEnd);
    this.on(this.$pickerTrack, 'pointercancel', onPointerEnd);

    this.on(this.$pickerTrack, 'click', (e) => {
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

      setIndex(indexOfValue(Number.parseInt(target.dataset.value, 10)), true);
    });

    this.on(this.$picker, 'keydown', (e) => {
      if (this.isActive) return;

      switch (e.key) {
        case 'ArrowLeft':
        case 'ArrowDown':
          e.preventDefault();
          setIndex(this.pickerIndex - 1);
          break;
        case 'ArrowRight':
        case 'ArrowUp':
          e.preventDefault();
          setIndex(this.pickerIndex + 1);
          break;
        case 'Home':
          e.preventDefault();
          setIndex(0);
          break;
        case 'End':
          e.preventDefault();
          setIndex(this.pickerValues.length - 1);
          break;
      }
    });

    this.on(
      this.$picker,
      'wheel',
      (e) => {
        if (this.isActive) return;
        if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) return;

        e.preventDefault();

        if (e.deltaX > 10) setIndex(this.pickerIndex + 1);
        else if (e.deltaX < -10) setIndex(this.pickerIndex - 1);
      },
      { passive: false }
    );

    setIndex(this.pickerIndex, false);
  }
}

export function setupBreathing(): void {
  mountAll('[data-component="breathing-app"]', (el) => new BreathingApp(el));
}
