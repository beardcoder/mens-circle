import { defineComponent } from '@beardcoder/lume';

export default defineComponent(({ part, signal, effect, on, root }) => {
  const button = part('control');
  const body = part('body');
  const open = signal(false);

  function toggle() {
    open.update((v) => !v);
  }

  on(button, 'click', toggle);

  effect(() => {
    button.setAttribute('aria-expanded', String(open()));
    body.style.maxBlockSize = open() ? body.scrollHeight + 'px' : '0';
    root.classList.toggle('accordion-item--open', open());
  });

  return {};
});
