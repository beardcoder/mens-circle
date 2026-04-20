/**
 * Scroll Animations
 *
 * Toggles `.is-visible` on sections and reveal targets as they enter the
 * viewport. This is a fallback for browsers without native CSS scroll-driven
 * animations (`animation-timeline: view()`), which handle the same effect
 * without touching the main thread.
 *
 * Everything animated here is composited-only (opacity + transform) — see
 * resources/css/utilities/_scroll-animations.css for the paired styles.
 */

import { defineComponent } from '@beardcoder/stitch-js';

interface ScrollAnimationsOptions {
  selector: string;
  visibleClass: string;
  rootMargin: string;
  threshold: number;
}

const supportsNativeScrollTimeline = (): boolean =>
  typeof CSS !== 'undefined' &&
  typeof CSS.supports === 'function' &&
  CSS.supports('animation-timeline', 'view()');

const prefersReducedMotion = (): boolean =>
  typeof globalThis.matchMedia === 'function' &&
  globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;

const REVEAL_SELECTOR = [
  '[data-reveal]',
  '.intro-section',
  '.moderator-section',
  '.journey-section',
  '.archetypes-section',
  '.testimonials-section',
  '.testimonial-form-section',
  '.faq-section',
  '.newsletter-section',
  '.whatsapp-section',
  '.cta-section',
  '.event-section',
  '.no-event-section',
  '.legal-section',
].join(',');

export const scrollAnimations = defineComponent<ScrollAnimationsOptions>(
  {
    selector: REVEAL_SELECTOR,
    visibleClass: 'is-visible',
    rootMargin: '0px 0px -10% 0px',
    threshold: 0.08,
  },
  (ctx) => {
    const { options: o } = ctx;
    const targets = Array.from(
      ctx.el.querySelectorAll<HTMLElement>(o.selector)
    );

    if (targets.length === 0) return;

    // If the browser supports native scroll-driven animations, the CSS handles
    // everything. Mark targets visible so the JS-driven baseline (hidden) is
    // ignored and the native animation can take over cleanly.
    if (supportsNativeScrollTimeline() || prefersReducedMotion()) {
      targets.forEach((el) => el.classList.add(o.visibleClass));

      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          entry.target.classList.add(o.visibleClass);
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: o.rootMargin, threshold: o.threshold }
    );

    targets.forEach((el) => {
      // Already above the fold when we initialize? Reveal immediately to
      // avoid a delayed animation after hydration.
      const rect = el.getBoundingClientRect();

      if (rect.top < globalThis.innerHeight * 0.9) {
        el.classList.add(o.visibleClass);

        return;
      }

      observer.observe(el);
    });

    ctx.onDestroy(() => observer.disconnect());
  }
);
