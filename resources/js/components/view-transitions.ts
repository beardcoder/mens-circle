import { supports } from 'view-transitions-toolkit/feature-detection';
import { useAutoTypes } from 'view-transitions-toolkit/navigation';

export function initViewTransitions(): void {
  if (!supports.crossDocument || !supports.types) return;

  useAutoTypes({
    home: '/',
    event: '/event/:slug',
    breathing: '/atemuebung',
    testimonial: '/teile-deine-erfahrung',
  });
}
