/**
 * Interactive breathing exercise (Wim Hof Method style)
 * Three-phase guided breathing: power breaths -> retention -> recovery hold
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
}

const PHASE_LABEL: Record<Phase, string> = {
  idle: 'Bereit',
  breathing: 'Atmen',
  retention: 'Halten',
  recovery: 'Erholung',
  complete: 'Geschafft',
};

function formatTime(seconds: number): string {
  const minutes = Math.floor(seconds / 60);
  const remainder = Math.floor(seconds % 60);

  return `${minutes.toString().padStart(2, '0')}:${remainder.toString().padStart(2, '0')}`;
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
    const settingBreaths = root.querySelector<HTMLInputElement>(
      '[data-element="settingBreaths"]'
    );
    const settingRounds = root.querySelector<HTMLInputElement>(
      '[data-element="settingRounds"]'
    );
    const settingRecovery = root.querySelector<HTMLInputElement>(
      '[data-element="settingRecovery"]'
    );

    if (!circle || !phaseEl || !counterEl || !startBtn) return;

    const state: BreathingState = {
      phase: 'idle',
      round: 0,
      breath: 0,
      retentionStart: 0,
      timerHandle: null,
      rafHandle: null,
    };

    const setPhaseClass = (phase: Phase): void => {
      circle.dataset.phase = phase;
      root.dataset.phase = phase;
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
      if (phaseEl) phaseEl.textContent = label;
    };

    const setCounter = (text: string): void => {
      if (counterEl) counterEl.textContent = text;
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
        cancelAnimationFrame(state.rafHandle);
        state.rafHandle = null;
      }
    };

    const setControls = (phase: Phase): void => {
      const showStart = phase === 'idle' || phase === 'complete';

      startBtn.hidden = !showStart;
      startBtn.textContent = phase === 'complete' ? 'Erneut starten' : 'Start';

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
      state.breath = 0;
      setPhaseClass('breathing');
      setLabel(PHASE_LABEL.breathing);
      setControls('breathing');
      updateMeta();

      const cycleMs = config.inhaleMs + config.exhaleMs;

      const inhale = (): void => {
        state.breath += 1;
        updateMeta();
        circle.dataset.motion = 'inhale';
        setCounter(`Einatmen · ${state.breath}`);
        state.timerHandle = window.setTimeout(exhale, config.inhaleMs);
      };

      const exhale = (): void => {
        circle.dataset.motion = 'exhale';
        setCounter(`Ausatmen · ${state.breath}`);

        state.timerHandle = window.setTimeout(() => {
          if (state.breath >= config.breaths) {
            circle.dataset.motion = 'hold';
            startRetention();

            return;
          }

          inhale();
        }, config.exhaleMs);
      };

      let elapsedSeconds = 0;
      const totalSeconds = (config.breaths * cycleMs) / 1000;

      setTimer(0);
      const timerTick = (): void => {
        elapsedSeconds += 1;
        setTimer(elapsedSeconds);

        if (state.phase === 'breathing' && elapsedSeconds < totalSeconds) {
          state.rafHandle = window.setTimeout(
            timerTick,
            1000
          ) as unknown as number;
        }
      };

      state.rafHandle = window.setTimeout(timerTick, 1000) as unknown as number;

      inhale();
    };

    const readSettings = (): void => {
      if (settingBreaths) {
        const value = Number.parseInt(settingBreaths.value, 10);

        if (Number.isFinite(value) && value > 0) config.breaths = value;
      }

      if (settingRounds) {
        const value = Number.parseInt(settingRounds.value, 10);

        if (Number.isFinite(value) && value > 0) config.rounds = value;
      }

      if (settingRecovery) {
        const value = Number.parseInt(settingRecovery.value, 10);

        if (Number.isFinite(value) && value > 0) config.recoveryHold = value;
      }
    };

    const handleStart = (): void => {
      readSettings();
      enterIdle();
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

    const lockSettings = (locked: boolean): void => {
      [settingBreaths, settingRounds, settingRecovery].forEach((input) => {
        if (input) input.disabled = locked;
      });
    };

    startBtn.addEventListener('click', () => {
      lockSettings(true);
      handleStart();
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
    });
  }
);
