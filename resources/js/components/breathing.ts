/**
 * Interactive breathing exercise (Wim Hof Method style).
 *
 * Three-phase guided breathing: power breaths → retention → recovery hold.
 * State machine: idle → breathing → retention → recovery → [next | complete]
 *
 * Factory style with signal-based reactivity. Each piece of session
 * state is a `signal()`; DOM bindings are `effect()`s that re-run
 * automatically when their dependencies change. The host's
 * `AbortSignal` disposes every effect on destroy.
 *
 * Settings are snapshotted into `session` when a session starts so
 * adjusting the picker / steppers mid-session has no effect on the
 * running round.
 */

import { clamp } from '@/utils/helpers';
import { createHost, mountAll, type Component } from '@/lib/host';
import { effect, signal, type Signal } from '@/lib/signal';

type Phase = 'idle' | 'breathing' | 'retention' | 'recovery' | 'complete';

interface SessionConfig {
  breaths: number;
  rounds: number;
  recoveryHold: number;
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

function requireRef<T extends HTMLElement>(
  scope: HTMLElement,
  name: string
): T {
  const el = scope.querySelector<T>(`[data-ref="${name}"]`);

  if (!el) throw new Error(`[breathing] missing [data-ref="${name}"]`);

  return el;
}

function circleMotion(phase: Phase): string | null {
  if (phase === 'breathing') return 'wave';
  if (phase === 'retention') return 'hold-high';
  if (phase === 'recovery') return 'hold-low';

  return null;
}

function createBreathing(root: HTMLElement): Component {
  const host = createHost(root);

  root.style.setProperty('--breathing-cycle-ms', `${CYCLE_MS}ms`);

  // ─── DOM refs ──────────────────────────────────────────────────────
  const $circle = requireRef<HTMLElement>(root, 'circle');
  const $phaseLabel = requireRef<HTMLElement>(root, 'phase-label');
  const $counter = requireRef<HTMLElement>(root, 'counter');
  const $metaRound = requireRef<HTMLElement>(root, 'meta-round');
  const $metaBreath = requireRef<HTMLElement>(root, 'meta-breath');
  const $metaTimer = requireRef<HTMLElement>(root, 'meta-timer');
  const $startBtn = requireRef<HTMLButtonElement>(root, 'start');
  const $holdBtn = requireRef<HTMLButtonElement>(root, 'hold');
  const $resetBtn = requireRef<HTMLButtonElement>(root, 'reset');
  const $picker = requireRef<HTMLElement>(root, 'picker');
  const $pickerTrack = requireRef<HTMLElement>(root, 'picker-track');
  const $roundsValue = requireRef<HTMLElement>(root, 'rounds-value');
  const $roundsMinus = requireRef<HTMLButtonElement>(root, 'rounds-minus');
  const $roundsPlus = requireRef<HTMLButtonElement>(root, 'rounds-plus');
  const $recoveryValue = requireRef<HTMLElement>(root, 'recovery-value');
  const $recoveryMinus = requireRef<HTMLButtonElement>(root, 'recovery-minus');
  const $recoveryPlus = requireRef<HTMLButtonElement>(root, 'recovery-plus');

  // ─── Reactive state ────────────────────────────────────────────────
  const phase = signal<Phase>('idle');
  const round = signal(0);
  const breath = signal(0);
  const timerSeconds = signal(0);
  const settingBreaths = signal(35);
  const settingRounds = signal(3);
  const settingRecovery = signal(15);
  const session = signal<SessionConfig | null>(null);

  const isActive = (): boolean => {
    const p = phase();

    return p === 'breathing' || p === 'retention' || p === 'recovery';
  };

  const sessionRoundsFn = (): number => session()?.rounds ?? settingRounds();
  const sessionBreathsFn = (): number => session()?.breaths ?? settingBreaths();

  const counterText = (): string => {
    switch (phase()) {
      case 'idle':
        return `${settingRounds()} Runden · ${settingBreaths()} Atemzüge`;
      case 'breathing':
        return `Atemzug ${breath()}`;
      case 'retention':
        return `Halten · ${formatTime(timerSeconds())}`;
      case 'recovery':
        return `Halten · ${timerSeconds()}s`;
      case 'complete':
        return 'Nimm dir einen Moment, spüre nach.';
    }
  };

  // ─── DOM bindings ──────────────────────────────────────────────────
  effect(() => {
    root.dataset.phase = phase();
  }, host.signal);

  effect(() => {
    const motion = circleMotion(phase());

    if (motion) $circle.dataset.motion = motion;
    else delete $circle.dataset.motion;
  }, host.signal);

  effect(() => {
    $phaseLabel.textContent = PHASE_LABEL[phase()];
  }, host.signal);

  effect(() => {
    $counter.textContent = counterText();
  }, host.signal);

  effect(() => {
    $metaRound.textContent = `${round()} / ${sessionRoundsFn()}`;
  }, host.signal);

  effect(() => {
    $metaBreath.textContent = `${breath()} / ${sessionBreathsFn()}`;
  }, host.signal);

  effect(() => {
    $metaTimer.textContent = formatTime(timerSeconds());
  }, host.signal);

  effect(() => {
    const p = phase();

    $startBtn.hidden = !(p === 'idle' || p === 'complete');
    $holdBtn.hidden = !(p === 'retention' || p === 'recovery');

    const startLabel =
      p === 'complete' ? 'Erneut starten' : 'Atemübung starten';

    $startBtn.setAttribute('aria-label', startLabel);
    $startBtn.title = startLabel;

    $holdBtn.textContent = p === 'recovery' ? 'Weiteratmen' : 'Atem freigeben';
  }, host.signal);

  effect(() => {
    $picker.setAttribute('aria-valuenow', String(settingBreaths()));

    if (isActive()) $picker.setAttribute('aria-disabled', 'true');
    else $picker.removeAttribute('aria-disabled');
  }, host.signal);

  effect(() => {
    $roundsValue.textContent = String(settingRounds());
    $roundsValue.setAttribute('aria-valuenow', String(settingRounds()));
    $roundsMinus.disabled = isActive() || settingRounds() <= 1;
    $roundsPlus.disabled = isActive() || settingRounds() >= 6;
  }, host.signal);

  effect(() => {
    $recoveryValue.textContent = String(settingRecovery());
    $recoveryValue.setAttribute('aria-valuenow', String(settingRecovery()));
    $recoveryMinus.disabled = isActive() || settingRecovery() <= 5;
    $recoveryPlus.disabled = isActive() || settingRecovery() >= 30;
  }, host.signal);

  // ─── Scheduling ────────────────────────────────────────────────────
  let timerHandle: number | null = null;
  let breathHandle: number | null = null;
  let rafHandle: number | null = null;

  const clearScheduled = (): void => {
    if (breathHandle !== null) {
      window.clearInterval(breathHandle);
      breathHandle = null;
    }

    if (timerHandle !== null) {
      window.clearTimeout(timerHandle);
      timerHandle = null;
    }

    if (rafHandle !== null) {
      cancelAnimationFrame(rafHandle);
      rafHandle = null;
    }
  };

  // ─── Phase transitions ─────────────────────────────────────────────
  const enterIdle = (): void => {
    clearScheduled();
    phase.set('idle');
    round.set(0);
    breath.set(0);
    timerSeconds.set(0);
    session.set(null);
  };

  const finishSession = (): void => {
    clearScheduled();
    phase.set('complete');
  };

  const startBreathing = (): void => {
    clearScheduled();
    phase.set('breathing');
    round.update((r) => r + 1);
    breath.set(1);
    timerSeconds.set(0);

    const startedAt = performance.now();
    const breathLimit = session()?.breaths ?? settingBreaths();

    breathHandle = window.setInterval(() => {
      if (phase() !== 'breathing') return;

      breath.update((b) => b + 1);

      if (breath() > breathLimit) startRetention();
    }, CYCLE_MS);

    const tick = (): void => {
      if (phase() !== 'breathing') return;

      timerSeconds.set(Math.floor((performance.now() - startedAt) / 1000));
      timerHandle = window.setTimeout(tick, 1000);
    };

    timerHandle = window.setTimeout(tick, 1000);
  };

  const startRetention = (): void => {
    clearScheduled();
    phase.set('retention');
    timerSeconds.set(0);

    const startedAt = performance.now();
    let lastSecond = -1;

    const loop = (): void => {
      if (phase() !== 'retention') return;

      const elapsed = Math.floor((performance.now() - startedAt) / 1000);

      if (elapsed !== lastSecond) {
        lastSecond = elapsed;
        timerSeconds.set(elapsed);
      }

      rafHandle = requestAnimationFrame(loop);
    };

    rafHandle = requestAnimationFrame(loop);
  };

  const startRecovery = (): void => {
    clearScheduled();
    phase.set('recovery');

    let remaining = session()?.recoveryHold ?? settingRecovery();

    timerSeconds.set(remaining);

    const tick = (): void => {
      remaining -= 1;
      timerSeconds.set(remaining);

      if (remaining <= 0) {
        const limit = session()?.rounds ?? settingRounds();

        if (round() >= limit) finishSession();
        else startBreathing();

        return;
      }

      timerHandle = window.setTimeout(tick, 1000);
    };

    timerHandle = window.setTimeout(tick, 1000);
  };

  const beginSession = (): void => {
    session.set({
      breaths: settingBreaths(),
      rounds: settingRounds(),
      recoveryHold: settingRecovery(),
    });
    round.set(0);
    breath.set(0);
    timerSeconds.set(0);
    startBreathing();
  };

  // ─── Actions ───────────────────────────────────────────────────────
  host.on($circle, 'click', () => {
    if (phase() === 'idle' || phase() === 'complete') beginSession();
  });

  host.on($startBtn, 'click', beginSession);

  host.on($holdBtn, 'click', () => {
    if (phase() === 'retention') {
      startRecovery();

      return;
    }

    if (phase() === 'recovery') {
      const limit = session()?.rounds ?? settingRounds();

      if (round() >= limit) finishSession();
      else startBreathing();
    }
  });

  host.on($resetBtn, 'click', enterIdle);

  host.on($roundsMinus, 'click', () => {
    if (!isActive()) settingRounds.update((n) => clamp(n - 1, 1, 6));
  });

  host.on($roundsPlus, 'click', () => {
    if (!isActive()) settingRounds.update((n) => clamp(n + 1, 1, 6));
  });

  host.on($recoveryMinus, 'click', () => {
    if (!isActive()) settingRecovery.update((n) => clamp(n - 1, 5, 30));
  });

  host.on($recoveryPlus, 'click', () => {
    if (!isActive()) settingRecovery.update((n) => clamp(n + 1, 5, 30));
  });

  // ─── iOS-style swipe picker ────────────────────────────────────────
  setupPicker({
    host,
    picker: $picker,
    track: $pickerTrack,
    isActive,
    breaths: settingBreaths,
  });

  return {
    destroy(): void {
      clearScheduled();
      host.destroy();
    },
  };
}

interface PickerArgs {
  host: ReturnType<typeof createHost>;
  picker: HTMLElement;
  track: HTMLElement;
  isActive: () => boolean;
  breaths: Signal<number>;
}

function setupPicker({
  host,
  picker,
  track,
  isActive,
  breaths,
}: PickerArgs): void {
  const values: number[] = [];

  for (let v = PICKER_MIN; v <= PICKER_MAX; v += PICKER_STEP) values.push(v);

  track.replaceChildren(
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
    track.querySelectorAll<HTMLElement>('.breathing-picker__item')
  );

  const indexOfValue = (v: number): number =>
    clamp(Math.round((v - PICKER_MIN) / PICKER_STEP), 0, values.length - 1);

  let currentIndex = indexOfValue(breaths());
  let dragOffset = 0;
  let pointerId: number | null = null;
  let dragStartX = 0;
  let dragStartIndex = 0;
  let dragged = false;

  const applyTransform = (animated: boolean): void => {
    track.style.transition = animated
      ? 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)'
      : 'none';
    track.style.transform = `translate3d(${
      -currentIndex * PICKER_ITEM_WIDTH + dragOffset
    }px, 0, 0)`;
  };

  const highlight = (index: number): void => {
    items.forEach((item, i) => {
      item.classList.toggle('is-active', i === index);
      item.tabIndex = i === index ? 0 : -1;
    });
  };

  const setIndex = (index: number, animated = true): void => {
    currentIndex = clamp(index, 0, values.length - 1);
    breaths.set(values[currentIndex] as number);
    highlight(currentIndex);
    applyTransform(animated);
  };

  host.on(track, 'pointerdown', (e) => {
    if (isActive()) return;
    if (e.pointerType === 'mouse' && e.button !== 0) return;

    pointerId = e.pointerId;
    dragStartX = e.clientX;
    dragStartIndex = currentIndex;
    dragged = false;
    track.setPointerCapture(e.pointerId);
    picker.classList.add('is-dragging');
  });

  host.on(track, 'pointermove', (e) => {
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
    picker.classList.remove('is-dragging');

    if (dragged) setIndex(dragStartIndex + indexDelta, true);
    else applyTransform(true);
  };

  host.on(track, 'pointerup', onPointerEnd);
  host.on(track, 'pointercancel', onPointerEnd);

  host.on(track, 'click', (e) => {
    if (isActive()) {
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

  host.on(picker, 'keydown', (e) => {
    if (isActive()) return;

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
  });

  host.on(
    picker,
    'wheel',
    (e) => {
      if (isActive()) return;
      if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) return;

      e.preventDefault();

      if (e.deltaX > 10) setIndex(currentIndex + 1);
      else if (e.deltaX < -10) setIndex(currentIndex - 1);
    },
    { passive: false }
  );

  setIndex(currentIndex, false);
}

export function setupBreathing(): void {
  mountAll('[data-component="breathing-app"]', createBreathing);
}
