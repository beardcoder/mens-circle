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

const WHEEL_ITEM_WIDTH = 56;

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
    const settingBreathsViewport = root.querySelector<HTMLElement>(
      '[data-element="settingBreathsViewport"]'
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

    // ---------- iOS-style swipe wheel ----------
    const setupWheel = (): (() => void) | undefined => {
      if (!settingBreaths || !settingBreathsViewport || !settingBreathsTrack)
        return;

      const min = Number.parseInt(settingBreaths.dataset.min ?? '10', 10);
      const max = Number.parseInt(settingBreaths.dataset.max ?? '60', 10);
      const initial = Number.parseInt(
        settingBreaths.dataset.value ?? String(min),
        10
      );

      settingBreathsTrack.textContent = '';
      for (let i = min; i <= max; i += 1) {
        const item = document.createElement('span');

        item.className = 'breathing-wheel__item';
        item.dataset.value = String(i);
        item.textContent = String(i);
        settingBreathsTrack.appendChild(item);
      }

      const applyPadding = (): void => {
        const width = settingBreathsViewport.clientWidth;
        const pad = Math.max(0, (width - WHEEL_ITEM_WIDTH) / 2);

        settingBreathsTrack.style.paddingInline = `${pad}px`;
      };

      const setActive = (value: number): void => {
        const clamped = clamp(value, min, max);

        settingBreaths.dataset.value = String(clamped);
        settingBreaths.setAttribute('aria-valuenow', String(clamped));

        const items = settingBreathsTrack.querySelectorAll<HTMLElement>(
          '.breathing-wheel__item'
        );

        items.forEach((item) => {
          item.classList.toggle(
            'is-active',
            item.dataset.value === String(clamped)
          );
        });
      };

      const scrollToValue = (value: number, smooth = true): void => {
        const index = clamp(value, min, max) - min;

        settingBreathsViewport.scrollTo({
          left: index * WHEEL_ITEM_WIDTH,
          behavior: smooth ? 'smooth' : 'auto',
        });
      };

      const valueFromScroll = (): number => {
        const index = Math.round(
          settingBreathsViewport.scrollLeft / WHEEL_ITEM_WIDTH
        );

        return clamp(index + min, min, max);
      };

      let scrollTimeout: number | null = null;
      const onScroll = (): void => {
        const value = valueFromScroll();

        setActive(value);

        if (scrollTimeout !== null) window.clearTimeout(scrollTimeout);
        scrollTimeout = window.setTimeout(() => {
          scrollToValue(value);
        }, 120);
      };

      settingBreathsViewport.addEventListener('scroll', onScroll, {
        passive: true,
      });

      settingBreathsTrack.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement).closest<HTMLElement>(
          '.breathing-wheel__item'
        );

        if (!target?.dataset.value) return;
        scrollToValue(Number.parseInt(target.dataset.value, 10));
      });

      settingBreaths.addEventListener('keydown', (event) => {
        if (settingBreaths.getAttribute('aria-disabled') === 'true') return;
        const current = Number.parseInt(
          settingBreaths.dataset.value ?? String(min),
          10
        );

        if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') {
          event.preventDefault();
          scrollToValue(current - 1);
        } else if (event.key === 'ArrowRight' || event.key === 'ArrowUp') {
          event.preventDefault();
          scrollToValue(current + 1);
        }
      });

      const onResize = (): void => {
        applyPadding();
        scrollToValue(
          Number.parseInt(settingBreaths.dataset.value ?? String(initial), 10),
          false
        );
      };

      window.addEventListener('resize', onResize);

      applyPadding();
      requestAnimationFrame(() => {
        scrollToValue(initial, false);
        setActive(initial);
      });

      return (): void => {
        window.removeEventListener('resize', onResize);
        if (scrollTimeout !== null) window.clearTimeout(scrollTimeout);
      };
    };

    const cleanupWheel = setupWheel();

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
