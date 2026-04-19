/**
 * Interactive breathing exercise (Wim Hof Method style)
 * Three-phase guided breathing: power breaths -> retention -> recovery hold.
 *
 * The breathing phase uses a continuous CSS keyframe animation so the rings
 * swell and settle like an ocean wave — no pauses between inhale and exhale.
 * TypeScript only counts breaths in sync with the cycle duration.
 */

import { defineComponent } from '@beardcoder/stitch-js';

interface BreathingOptions {
  breaths: number;
  rounds: number;
  recoveryHold: number;
  inhaleMs: number;
  exhaleMs: number;
}

type Phase = 'idle' | 'breathing' | 'retention' | 'recovery' | 'complete';

interface BreathingState {
  phase: Phase;
  round: number;
  breath: number;
  retentionStart: number;
  timerHandle: number | null;
  rafHandle: number | null;
  breathHandle: number | null;
}

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

export const breathingApp = defineComponent<BreathingOptions>(
  {
    breaths: 30,
    rounds: 3,
    recoveryHold: 15,
    inhaleMs: 1800,
    exhaleMs: 1800,
  },
  (ctx) => {
    const root = ctx.el;
    const config: BreathingOptions = { ...ctx.options };

    const circle = root.querySelector<HTMLElement>('[data-element="circle"]');
    const phaseEl = root.querySelector<HTMLElement>('[data-element="phase"]');
    const counterEl = root.querySelector<HTMLElement>(
      '[data-element="counter"]'
    );
    const roundEl = root.querySelector<HTMLElement>('[data-element="round"]');
    const breathEl = root.querySelector<HTMLElement>('[data-element="breath"]');
    const timerEl = root.querySelector<HTMLElement>('[data-element="timer"]');
    const startBtn = root.querySelector<HTMLButtonElement>(
      '[data-element="start"]'
    );
    const holdBtn = root.querySelector<HTMLButtonElement>(
      '[data-element="hold"]'
    );
    const resetBtn = root.querySelector<HTMLButtonElement>(
      '[data-element="reset"]'
    );
    const settingBreaths = root.querySelector<HTMLElement>(
      '[data-element="settingBreaths"]'
    );
    const settingBreathsTrack = root.querySelector<HTMLElement>(
      '[data-element="settingBreathsTrack"]'
    );
    const settingRounds = root.querySelector<HTMLElement>(
      '[data-element="settingRounds"]'
    );
    const settingRoundsMinus = root.querySelector<HTMLButtonElement>(
      '[data-element="settingRoundsMinus"]'
    );
    const settingRoundsPlus = root.querySelector<HTMLButtonElement>(
      '[data-element="settingRoundsPlus"]'
    );
    const settingRecovery = root.querySelector<HTMLElement>(
      '[data-element="settingRecovery"]'
    );
    const settingRecoveryMinus = root.querySelector<HTMLButtonElement>(
      '[data-element="settingRecoveryMinus"]'
    );
    const settingRecoveryPlus = root.querySelector<HTMLButtonElement>(
      '[data-element="settingRecoveryPlus"]'
    );

    if (!circle || !phaseEl || !counterEl || !startBtn) return;

    const cycleMs = config.inhaleMs + config.exhaleMs;

    root.style.setProperty('--breathing-cycle-ms', `${cycleMs}ms`);

    const state: BreathingState = {
      phase: 'idle',
      round: 0,
      breath: 0,
      retentionStart: 0,
      timerHandle: null,
      rafHandle: null,
      breathHandle: null,
    };

    const setPhaseClass = (phase: Phase): void => {
      circle.dataset.phase = phase;
      root.dataset.phase = phase;

      if (phase === 'breathing') {
        circle.dataset.motion = 'wave';
      } else if (phase === 'retention') {
        circle.dataset.motion = 'hold-high';
      } else if (phase === 'recovery') {
        circle.dataset.motion = 'hold-low';
      } else {
        delete circle.dataset.motion;
      }
    };

    const updateMeta = (): void => {
      if (roundEl) {
        roundEl.innerHTML = `${state.round}&nbsp;/&nbsp;${config.rounds}`;
      }

      if (breathEl) {
        breathEl.innerHTML = `${state.breath}&nbsp;/&nbsp;${config.breaths}`;
      }
    };

    const setLabel = (label: string): void => {
      phaseEl.textContent = label;
    };

    const setCounter = (text: string): void => {
      counterEl.textContent = text;
    };

    const setTimer = (seconds: number): void => {
      if (timerEl) timerEl.textContent = formatTime(seconds);
    };

    const clearScheduled = (): void => {
      if (state.timerHandle !== null) {
        window.clearTimeout(state.timerHandle);
        state.timerHandle = null;
      }

      if (state.rafHandle !== null) {
        window.clearTimeout(state.rafHandle);
        cancelAnimationFrame(state.rafHandle);
        state.rafHandle = null;
      }

      if (state.breathHandle !== null) {
        window.clearInterval(state.breathHandle);
        state.breathHandle = null;
      }
    };

    const setControls = (phase: Phase): void => {
      const showStart = phase === 'idle' || phase === 'complete';

      startBtn.hidden = !showStart;

      const label =
        phase === 'complete' ? 'Erneut starten' : 'Atemübung starten';

      startBtn.setAttribute('aria-label', label);
      startBtn.setAttribute('title', label);

      if (holdBtn) {
        holdBtn.hidden = !(phase === 'retention' || phase === 'recovery');

        holdBtn.textContent =
          phase === 'recovery' ? 'Weiteratmen' : 'Atem freigeben';
      }
    };

    const enterIdle = (): void => {
      clearScheduled();
      state.phase = 'idle';
      state.round = 0;
      state.breath = 0;
      setPhaseClass('idle');
      setLabel(PHASE_LABEL.idle);
      setCounter(`${config.rounds} Runden · ${config.breaths} Atemzüge`);
      setTimer(0);
      updateMeta();
      setControls('idle');
    };

    const finish = (): void => {
      clearScheduled();
      state.phase = 'complete';
      setPhaseClass('complete');
      setLabel(PHASE_LABEL.complete);
      setCounter('Nimm dir einen Moment, spüre nach.');
      setControls('complete');
    };

    const startRecovery = (): void => {
      state.phase = 'recovery';
      setPhaseClass('recovery');
      setLabel(PHASE_LABEL.recovery);
      setControls('recovery');

      let remaining = config.recoveryHold;

      setCounter(`Halten · ${remaining}s`);
      setTimer(remaining);

      const tick = (): void => {
        remaining -= 1;

        if (remaining <= 0) {
          if (state.round >= config.rounds) {
            finish();

            return;
          }

          startBreathing();

          return;
        }

        setCounter(`Halten · ${remaining}s`);
        setTimer(remaining);
        state.timerHandle = window.setTimeout(tick, 1000);
      };

      state.timerHandle = window.setTimeout(tick, 1000);
    };

    const startRetention = (): void => {
      state.phase = 'retention';
      setPhaseClass('retention');
      setLabel(PHASE_LABEL.retention);
      setControls('retention');
      state.retentionStart = performance.now();

      const tick = (): void => {
        const elapsed = Math.floor(
          (performance.now() - state.retentionStart) / 1000
        );

        setCounter(`Halten · ${formatTime(elapsed)}`);
        setTimer(elapsed);
        state.rafHandle = requestAnimationFrame(tick);
      };

      state.rafHandle = requestAnimationFrame(tick);
    };

    const startBreathing = (): void => {
      state.phase = 'breathing';
      state.round += 1;
      state.breath = 1;
      setPhaseClass('breathing');
      setLabel(PHASE_LABEL.breathing);
      setControls('breathing');
      setCounter(`Atemzug ${state.breath}`);
      updateMeta();

      const startedAt = performance.now();

      const tickBreath = (): void => {
        if (state.phase !== 'breathing') return;

        state.breath += 1;

        if (state.breath > config.breaths) {
          startRetention();

          return;
        }

        setCounter(`Atemzug ${state.breath}`);
        updateMeta();
      };

      state.breathHandle = window.setInterval(tickBreath, cycleMs);

      setTimer(0);
      const timerTick = (): void => {
        if (state.phase !== 'breathing') return;

        const elapsed = Math.floor((performance.now() - startedAt) / 1000);

        setTimer(elapsed);
        state.rafHandle = window.setTimeout(
          timerTick,
          1000
        ) as unknown as number;
      };

      state.rafHandle = window.setTimeout(timerTick, 1000) as unknown as number;
    };

    // ============================================
    // Settings: stepper + wheel
    // ============================================
    const readInt = (el: HTMLElement | null, fallback: number): number => {
      if (!el) return fallback;
      const value = Number.parseInt(el.dataset.value ?? '', 10);

      return Number.isFinite(value) ? value : fallback;
    };

    const readSettings = (): void => {
      config.breaths = readInt(settingBreaths, config.breaths);
      config.rounds = readInt(settingRounds, config.rounds);
      config.recoveryHold = readInt(settingRecovery, config.recoveryHold);
    };

    const updateStepper = (el: HTMLElement, value: number): void => {
      el.dataset.value = String(value);
      el.textContent = String(value);
      el.setAttribute('aria-valuenow', String(value));
    };

    const bindStepper = (
      valueEl: HTMLElement | null,
      minus: HTMLButtonElement | null,
      plus: HTMLButtonElement | null
    ): void => {
      if (!valueEl) return;

      const min = Number.parseInt(valueEl.dataset.min ?? '0', 10);
      const max = Number.parseInt(valueEl.dataset.max ?? '100', 10);

      const step = (delta: number): void => {
        if (valueEl.getAttribute('aria-disabled') === 'true') return;
        const current = Number.parseInt(valueEl.dataset.value ?? '0', 10);
        const next = clamp(current + delta, min, max);

        if (next === current) return;
        updateStepper(valueEl, next);
      };

      minus?.addEventListener('click', () => step(-1));
      plus?.addEventListener('click', () => step(1));
    };

    bindStepper(settingRounds, settingRoundsMinus, settingRoundsPlus);
    bindStepper(settingRecovery, settingRecoveryMinus, settingRecoveryPlus);

    // ---------- iOS-style swipe picker (pointer-drag, stepped) ----------
    const setupPicker = (): (() => void) | undefined => {
      if (!settingBreaths || !settingBreathsTrack) return;

      const min = Number.parseInt(settingBreaths.dataset.min ?? '10', 10);
      const max = Number.parseInt(settingBreaths.dataset.max ?? '60', 10);
      const step = Math.max(
        1,
        Number.parseInt(settingBreaths.dataset.step ?? '5', 10)
      );
      const initial = Number.parseInt(
        settingBreaths.dataset.value ?? String(min),
        10
      );

      const values: number[] = [];

      for (let v = min; v <= max; v += step) values.push(v);

      settingBreathsTrack.textContent = '';
      values.forEach((value) => {
        const item = document.createElement('button');

        item.type = 'button';
        item.className = 'breathing-picker__item';
        item.dataset.value = String(value);
        item.textContent = String(value);
        item.setAttribute('aria-label', `${value} Atemzüge`);
        settingBreathsTrack.appendChild(item);
      });

      const items = Array.from(
        settingBreathsTrack.querySelectorAll<HTMLElement>(
          '.breathing-picker__item'
        )
      );

      const indexOfValue = (value: number): number => {
        const snapped = Math.round((value - min) / step);

        return clamp(snapped, 0, values.length - 1);
      };

      let currentIndex = indexOfValue(initial);
      let dragOffset = 0;
      let pointerId: number | null = null;
      let dragStartX = 0;
      let dragStartIndex = 0;
      let dragged = false;

      const applyTransform = (animated: boolean): void => {
        settingBreathsTrack.style.transition = animated
          ? 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)'
          : 'none';
        const base = -currentIndex * PICKER_ITEM_WIDTH;

        settingBreathsTrack.style.transform = `translate3d(${base + dragOffset}px, 0, 0)`;
      };

      const highlight = (index: number): void => {
        items.forEach((item, i) => {
          item.classList.toggle('is-active', i === index);
          item.tabIndex = i === index ? 0 : -1;
        });
      };

      const setIndex = (index: number, animated = true): void => {
        currentIndex = clamp(index, 0, values.length - 1);
        const value = values[currentIndex] ?? min;

        settingBreaths.dataset.value = String(value);
        settingBreaths.setAttribute('aria-valuenow', String(value));
        highlight(currentIndex);
        applyTransform(animated);
      };

      const onPointerDown = (event: PointerEvent): void => {
        if (settingBreaths.getAttribute('aria-disabled') === 'true') return;
        if (event.pointerType === 'mouse' && event.button !== 0) return;

        pointerId = event.pointerId;
        dragStartX = event.clientX;
        dragStartIndex = currentIndex;
        dragged = false;
        settingBreathsTrack.setPointerCapture(event.pointerId);
        settingBreaths.classList.add('is-dragging');
      };

      const onPointerMove = (event: PointerEvent): void => {
        if (pointerId !== event.pointerId) return;

        const delta = event.clientX - dragStartX;

        if (!dragged && Math.abs(delta) > 4) dragged = true;
        dragOffset = delta;
        applyTransform(false);
      };

      const onPointerEnd = (event: PointerEvent): void => {
        if (pointerId !== event.pointerId) return;

        const delta = dragOffset;
        const indexDelta = Math.round(-delta / PICKER_ITEM_WIDTH);

        pointerId = null;
        dragOffset = 0;
        settingBreaths.classList.remove('is-dragging');

        if (dragged) {
          setIndex(dragStartIndex + indexDelta, true);
        } else {
          applyTransform(true);
        }
      };

      settingBreathsTrack.addEventListener('pointerdown', onPointerDown);
      settingBreathsTrack.addEventListener('pointermove', onPointerMove);
      settingBreathsTrack.addEventListener('pointerup', onPointerEnd);
      settingBreathsTrack.addEventListener('pointercancel', onPointerEnd);

      settingBreathsTrack.addEventListener('click', (event) => {
        if (dragged) {
          event.preventDefault();
          event.stopPropagation();
          dragged = false;

          return;
        }

        const target = (event.target as HTMLElement).closest<HTMLElement>(
          '.breathing-picker__item'
        );

        if (!target?.dataset.value) return;

        const index = indexOfValue(Number.parseInt(target.dataset.value, 10));

        setIndex(index, true);
      });

      settingBreaths.addEventListener('keydown', (event) => {
        if (settingBreaths.getAttribute('aria-disabled') === 'true') return;

        if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
          event.preventDefault();
          setIndex(currentIndex - 1, true);
        } else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
          event.preventDefault();
          setIndex(currentIndex + 1, true);
        } else if (event.key === 'Home') {
          event.preventDefault();
          setIndex(0, true);
        } else if (event.key === 'End') {
          event.preventDefault();
          setIndex(values.length - 1, true);
        }
      });

      // Trackpad / mouse wheel horizontal swipe
      settingBreaths.addEventListener(
        'wheel',
        (event) => {
          if (settingBreaths.getAttribute('aria-disabled') === 'true') return;
          if (Math.abs(event.deltaX) < Math.abs(event.deltaY)) return;
          event.preventDefault();
          if (event.deltaX > 10) setIndex(currentIndex + 1, true);
          else if (event.deltaX < -10) setIndex(currentIndex - 1, true);
        },
        { passive: false }
      );

      setIndex(currentIndex, false);

      return (): void => {
        settingBreathsTrack.removeEventListener('pointerdown', onPointerDown);
        settingBreathsTrack.removeEventListener('pointermove', onPointerMove);
        settingBreathsTrack.removeEventListener('pointerup', onPointerEnd);
        settingBreathsTrack.removeEventListener('pointercancel', onPointerEnd);
      };
    };

    const cleanupWheel = setupPicker();

    const lockSettings = (locked: boolean): void => {
      [
        settingBreaths,
        settingRounds,
        settingRecovery,
        settingRoundsMinus,
        settingRoundsPlus,
        settingRecoveryMinus,
        settingRecoveryPlus,
      ].forEach((el) => {
        if (!el) return;
        if ('disabled' in el) {
          (el as HTMLButtonElement).disabled = locked;
        }
        if (locked) {
          el.setAttribute('aria-disabled', 'true');
        } else {
          el.removeAttribute('aria-disabled');
        }
      });
    };

    const handleStart = (): void => {
      readSettings();
      const nextCycle = config.inhaleMs + config.exhaleMs;

      root.style.setProperty('--breathing-cycle-ms', `${nextCycle}ms`);
      state.round = 0;
      state.breath = 0;
      setTimer(0);
      startBreathing();
    };

    const handleHoldButton = (): void => {
      if (state.phase === 'retention') {
        startRecovery();

        return;
      }

      if (state.phase === 'recovery') {
        clearScheduled();

        if (state.round >= config.rounds) {
          finish();

          return;
        }

        startBreathing();
      }
    };

    const handleReset = (): void => {
      enterIdle();
    };

    startBtn.addEventListener('click', () => {
      lockSettings(true);
      handleStart();
    });

    circle.addEventListener('click', () => {
      if (state.phase === 'idle' || state.phase === 'complete') {
        lockSettings(true);
        handleStart();
      }
    });

    if (holdBtn) {
      holdBtn.addEventListener('click', handleHoldButton);
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        lockSettings(false);
        handleReset();
      });
    }

    enterIdle();

    ctx.onDestroy(() => {
      clearScheduled();
      cleanupWheel?.();
    });
  }
);
