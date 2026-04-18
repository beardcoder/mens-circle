import { defineComponent } from '@beardcoder/stitch-js';

type BreathingPhaseKey = 'inhale' | 'hold' | 'exhale' | 'pause';

interface BreathingAppOptions {
  startSelector: string;
  pauseSelector: string;
  resetSelector: string;
  phaseSelector: string;
  instructionSelector: string;
  timerSelector: string;
  roundSelector: string;
  stateSelector: string;
  inputSelector: string;
  outputSelector: string;
}

interface BreathingPhase {
  key: BreathingPhaseKey;
  duration: number;
  label: string;
  instruction: string;
}

interface BreathingState {
  isRunning: boolean;
  isCompleted: boolean;
  phaseIndex: number;
  roundsCompleted: number;
  roundsTarget: number;
  secondsRemaining: number;
  timerId: number | null;
}

const phaseMeta: Record<BreathingPhaseKey, Omit<BreathingPhase, 'duration'>> = {
  inhale: {
    key: 'inhale',
    label: 'Einatmen',
    instruction: 'Atme tief und ruhig durch die Nase ein.',
  },
  hold: {
    key: 'hold',
    label: 'Halten',
    instruction: 'Halte sanft die Fülle, ohne Druck aufzubauen.',
  },
  exhale: {
    key: 'exhale',
    label: 'Ausatmen',
    instruction: 'Lass den Atem lang und weich wieder ausströmen.',
  },
  pause: {
    key: 'pause',
    label: 'Ruhe',
    instruction: 'Bleibe kurz in der Stille, bevor die nächste Runde beginnt.',
  },
};

export const breathingApp = defineComponent<BreathingAppOptions>(
  {
    startSelector: '[data-breathing-start]',
    pauseSelector: '[data-breathing-pause]',
    resetSelector: '[data-breathing-reset]',
    phaseSelector: '[data-breathing-phase]',
    instructionSelector: '[data-breathing-instruction]',
    timerSelector: '[data-breathing-timer]',
    roundSelector: '[data-breathing-round]',
    stateSelector: '[data-breathing-state]',
    inputSelector: '[data-breathing-input]',
    outputSelector: '[data-breathing-output]',
  },
  (ctx) => {
    const { options: o } = ctx;

    const startButton = ctx.el.querySelector<HTMLButtonElement>(
      o.startSelector
    );
    const pauseButton = ctx.el.querySelector<HTMLButtonElement>(
      o.pauseSelector
    );
    const resetButton = ctx.el.querySelector<HTMLButtonElement>(
      o.resetSelector
    );
    const phaseElement = ctx.el.querySelector<HTMLElement>(o.phaseSelector);
    const instructionElement = ctx.el.querySelector<HTMLElement>(
      o.instructionSelector
    );
    const timerElement = ctx.el.querySelector<HTMLElement>(o.timerSelector);
    const roundElement = ctx.el.querySelector<HTMLElement>(o.roundSelector);
    const stateElement = ctx.el.querySelector<HTMLElement>(o.stateSelector);
    const inputElements = ctx.el.querySelectorAll<HTMLInputElement>(
      o.inputSelector
    );

    if (
      !startButton ||
      !pauseButton ||
      !resetButton ||
      !phaseElement ||
      !instructionElement ||
      !timerElement ||
      !roundElement ||
      !stateElement ||
      inputElements.length === 0
    ) {
      return;
    }

    const inputMap = Array.from(inputElements).reduce<
      Partial<Record<BreathingPhaseKey | 'rounds', HTMLInputElement>>
    >((carry, input) => {
      const key = input.dataset.breathingInput as
        | BreathingPhaseKey
        | 'rounds'
        | undefined;

      if (key) {
        carry[key] = input;
      }

      return carry;
    }, {});

    const outputMap = Array.from(
      ctx.el.querySelectorAll<HTMLOutputElement>(o.outputSelector)
    ).reduce<Partial<Record<BreathingPhaseKey | 'rounds', HTMLOutputElement>>>(
      (carry, output) => {
        const key = output.dataset.breathingOutput as
          | BreathingPhaseKey
          | 'rounds'
          | undefined;

        if (key) {
          carry[key] = output;
        }

        return carry;
      },
      {}
    );

    const state: BreathingState = {
      isRunning: false,
      isCompleted: false,
      phaseIndex: 0,
      roundsCompleted: 0,
      roundsTarget: 10,
      secondsRemaining: 0,
      timerId: null,
    };

    const readInputValue = (key: BreathingPhaseKey | 'rounds'): number => {
      const fallback = Number.parseInt(
        ctx.el.dataset[
          `default${key.charAt(0).toUpperCase()}${key.slice(1)}`
        ] ?? '0',
        10
      );
      const value = inputMap[key]?.value ?? String(fallback);
      const parsedValue = Number.parseInt(value, 10);

      return Number.isNaN(parsedValue) ? fallback : parsedValue;
    };

    const getPhases = (): BreathingPhase[] =>
      (['inhale', 'hold', 'exhale', 'pause'] as const)
        .map((key) => ({
          ...phaseMeta[key],
          duration: readInputValue(key),
        }))
        .filter((phase) => phase.duration > 0);

    const updateOutputs = (): void => {
      (Object.keys(outputMap) as Array<BreathingPhaseKey | 'rounds'>).forEach(
        (key) => {
          const output = outputMap[key];

          if (!output) {
            return;
          }

          const value = readInputValue(key);

          output.textContent = key === 'rounds' ? String(value) : `${value}s`;
        }
      );
    };

    const clearTimer = (): void => {
      if (state.timerId === null) {
        return;
      }

      window.clearInterval(state.timerId);
      state.timerId = null;
    };

    const updateRoundDisplay = (): void => {
      roundElement.textContent = `${Math.min(
        state.roundsCompleted,
        state.roundsTarget
      )} / ${state.roundsTarget}`;
    };

    const setStateLabel = (): void => {
      if (state.isCompleted) {
        stateElement.textContent = 'Runde abgeschlossen';

        return;
      }

      if (state.isRunning) {
        stateElement.textContent = 'Läuft';

        return;
      }

      stateElement.textContent =
        state.roundsCompleted > 0 ? 'Pausiert' : 'Wartet auf Start';
    };

    const updateButtons = (): void => {
      pauseButton.disabled = !state.isRunning;
      startButton.textContent =
        state.roundsCompleted > 0 && !state.isCompleted ? 'Weiter' : 'Start';
    };

    const syncPhaseUi = (phase: BreathingPhase): void => {
      ctx.el.classList.remove(
        'breathing-app--inhale',
        'breathing-app--hold',
        'breathing-app--exhale',
        'breathing-app--pause',
        'breathing-app--idle',
        'breathing-app--complete'
      );

      ctx.el.classList.add(`breathing-app--${phase.key}`);
      ctx.el.style.setProperty(
        '--breathing-phase-duration',
        `${phase.duration}s`
      );
      phaseElement.textContent = phase.label;
      instructionElement.textContent = phase.instruction;
      timerElement.textContent = String(state.secondsRemaining);
    };

    const primePhase = (): void => {
      const phases = getPhases();

      if (phases.length === 0) {
        return;
      }

      if (state.phaseIndex >= phases.length) {
        state.phaseIndex = 0;
      }

      const phase = phases[state.phaseIndex];

      state.secondsRemaining = phase.duration;
      syncPhaseUi(phase);
      updateRoundDisplay();
      setStateLabel();
      updateButtons();
    };

    const completeSession = (): void => {
      clearTimer();
      state.isRunning = false;
      state.isCompleted = true;
      state.secondsRemaining = 0;
      ctx.el.classList.remove(
        'breathing-app--inhale',
        'breathing-app--hold',
        'breathing-app--exhale',
        'breathing-app--pause',
        'breathing-app--idle'
      );
      ctx.el.classList.add('breathing-app--complete');
      phaseElement.textContent = 'Geschafft';
      instructionElement.textContent =
        'Spüre kurz nach und genieße die Ruhe nach deiner Atemrunde.';
      timerElement.textContent = '0';
      state.roundsCompleted = state.roundsTarget;
      updateRoundDisplay();
      setStateLabel();
      updateButtons();
    };

    const advancePhase = (): void => {
      const phases = getPhases();

      if (phases.length === 0) {
        return;
      }

      if (state.phaseIndex >= phases.length - 1) {
        state.phaseIndex = 0;
        state.roundsCompleted += 1;

        if (state.roundsCompleted >= state.roundsTarget) {
          completeSession();

          return;
        }
      } else {
        state.phaseIndex += 1;
      }

      const nextPhase = phases[state.phaseIndex];

      state.secondsRemaining = nextPhase.duration;
      syncPhaseUi(nextPhase);
      updateRoundDisplay();
    };

    const tick = (): void => {
      if (!state.isRunning) {
        return;
      }

      state.secondsRemaining -= 1;

      if (state.secondsRemaining <= 0) {
        advancePhase();

        return;
      }

      timerElement.textContent = String(state.secondsRemaining);
    };

    const start = (): void => {
      if (state.isRunning) {
        return;
      }

      state.roundsTarget = readInputValue('rounds');

      if (state.isCompleted) {
        state.phaseIndex = 0;
        state.roundsCompleted = 0;
      }

      if (state.secondsRemaining <= 0) {
        primePhase();
      }

      state.isCompleted = false;
      state.isRunning = true;
      setStateLabel();
      updateButtons();
      clearTimer();
      state.timerId = window.setInterval(tick, 1000);
    };

    const pause = (): void => {
      if (!state.isRunning) {
        return;
      }

      state.isRunning = false;
      clearTimer();
      setStateLabel();
      updateButtons();
    };

    const reset = (): void => {
      clearTimer();
      state.isRunning = false;
      state.isCompleted = false;
      state.phaseIndex = 0;
      state.roundsCompleted = 0;
      state.roundsTarget = readInputValue('rounds');
      ctx.el.classList.remove(
        'breathing-app--inhale',
        'breathing-app--hold',
        'breathing-app--exhale',
        'breathing-app--pause',
        'breathing-app--complete'
      );
      ctx.el.classList.add('breathing-app--idle');
      primePhase();
    };

    updateOutputs();
    reset();

    startButton.addEventListener('click', start);
    pauseButton.addEventListener('click', pause);
    resetButton.addEventListener('click', reset);

    inputElements.forEach((input) => {
      const handleInput = (): void => {
        updateOutputs();

        if (state.isRunning) {
          return;
        }

        if (input.dataset.breathingInput === 'rounds') {
          state.roundsTarget = readInputValue('rounds');
        }

        primePhase();
      };

      input.addEventListener('input', handleInput);
      ctx.onDestroy(() => input.removeEventListener('input', handleInput));
    });

    ctx.onDestroy(() => {
      clearTimer();
      startButton.removeEventListener('click', start);
      pauseButton.removeEventListener('click', pause);
      resetButton.removeEventListener('click', reset);
    });
  }
);
