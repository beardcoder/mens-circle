/**
 * Composables for Men's Circle App
 * Modern, reusable functionality with better separation of concerns
 */

import { animate, inView, scroll, stagger } from 'motion';

export interface AnimationOptions {
  threshold?: number;
  amount?: number;
  rootMargin?: string;
}

/**
 * Modern Intersection Observer wrapper using Motion One
 */
export function useIntersectionObserver(
  options: AnimationOptions = {}
): void {
  const { threshold = 0.1, amount = 0.3 } = options;

  // Fade in animations for sections
  const sections = document.querySelectorAll<HTMLElement>(
    '[data-animate="fade-in"]'
  );
  sections.forEach((section) => {
    inView(
      section,
      () => {
        animate(
          section,
          { opacity: [0, 1], y: [20, 0] } as any,
          { duration: 0.6, easing: 'ease-out' } as any
        );
      },
      { amount }
    );
  });

  // Staggered animations for lists
  const staggeredLists =
    document.querySelectorAll<HTMLElement>('[data-animate="stagger"]');
  staggeredLists.forEach((list) => {
    const items = list.querySelectorAll<HTMLElement>('[data-animate-item]');
    inView(
      list,
      () => {
        animate(
          items,
          { opacity: [0, 1], y: [20, 0] } as any,
          { duration: 0.5, delay: stagger(0.1), easing: 'ease-out' } as any
        );
      },
      { amount: threshold }
    );
  });

  // Scale animations
  const scaleElements = document.querySelectorAll<HTMLElement>(
    '[data-animate="scale"]'
  );
  scaleElements.forEach((el) => {
    inView(
      el,
      () => {
        animate(
          el,
          { opacity: [0, 1], scale: [0.9, 1] } as any,
          { duration: 0.5, easing: 'ease-out' } as any
        );
      },
      { amount }
    );
  });
}

/**
 * Smooth scroll with Motion One
 */
export function useSmoothScroll(): void {
  const links = document.querySelectorAll<HTMLAnchorElement>(
    'a[href^="#"]:not([href="#"])'
  );

  links.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = link.getAttribute('href');
      if (!targetId) return;

      const target = document.querySelector<HTMLElement>(targetId);
      if (!target) return;

      animate(
        window.scrollY,
        target.offsetTop - 80,
        {
          duration: 0.8,
          easing: [0.25, 0.1, 0.25, 1] as any,
          onUpdate: (value: number) => window.scrollTo(0, value),
        } as any
      );
    });
  });
}

/**
 * Parallax scroll effects using Motion One
 */
export function useParallax(): void {
  const parallaxElements = document.querySelectorAll<HTMLElement>(
    '[data-parallax]'
  );

  parallaxElements.forEach((el) => {
    const speed = parseFloat(el.dataset.parallax || '0.5');

    scroll(animate(el, { y: [0, -100 * speed] } as any), {
      target: el,
      offset: ['start end', 'end start'],
    });
  });
}

/**
 * View Transitions API support - Modern page transitions
 */
export function useViewTransitions(): void {
  // Check browser support
  if (!('startViewTransition' in document)) {
    console.debug('View Transitions API not supported');
    return;
  }

  // Intercept same-origin navigation
  window.addEventListener('click', (e) => {
    const link = (e.target as HTMLElement).closest<HTMLAnchorElement>('a');
    
    // Skip if not a valid link for transition
    if (!shouldTransition(link)) {
      return;
    }

    e.preventDefault();
    
    const url = link!.href;
    
    // Start view transition
    (document as any).startViewTransition(async () => {
      // Fetch new page
      const response = await fetch(url);
      const html = await response.text();
      
      // Parse the new HTML
      const parser = new DOMParser();
      const newDoc = parser.parseFromString(html, 'text/html');
      
      // Update page title
      document.title = newDoc.title;
      
      // Update main content
      const main = document.querySelector('main');
      const newMain = newDoc.querySelector('main');
      if (main && newMain) {
        main.innerHTML = newMain.innerHTML;
      }
      
      // Update page URL
      window.history.pushState({}, '', url);
      
      // Reinitialize scripts for new content
      window.dispatchEvent(new Event('content-updated'));
    });
  });
}

function shouldTransition(link: HTMLAnchorElement | null): boolean {
  if (!link) return false;
  
  // Check if it's a valid link
  if (!link.href || link.target === '_blank') return false;
  
  // Check if it's same origin
  if (link.origin !== location.origin) return false;
  
  // Check for no-transition attribute
  if (link.hasAttribute('data-no-transition')) return false;
  
  // Check for download attribute
  if (link.hasAttribute('download')) return false;
  
  // Skip hash links (anchor navigation)
  if (link.getAttribute('href')?.startsWith('#')) return false;
  
  // Skip mailto and tel links
  if (link.href.startsWith('mailto:') || link.href.startsWith('tel:')) return false;
  
  return true;
}

/**
 * Enhanced form handling with better UX
 */
export interface FormOptions {
  onSubmit: (data: FormData) => Promise<Response>;
  onSuccess?: (data: any) => void;
  onError?: (error: Error) => void;
}

export function useForm(
  formElement: HTMLFormElement,
  options: FormOptions
): void {
  const submitButton =
    formElement.querySelector<HTMLButtonElement>('[type="submit"]');
  const originalButtonText = submitButton?.textContent ?? '';

  formElement.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Wird gesendet...';
    }

    const formData = new FormData(formElement);

    try {
      const response = await options.onSubmit(formData);
      const data = await response.json();

      if (data.success) {
        formElement.reset();
        options.onSuccess?.(data);
        showToast('success', data.message);
      } else {
        options.onError?.(new Error(data.message));
        showToast('error', data.message);
      }
    } catch (error) {
      options.onError?.(error as Error);
      showToast(
        'error',
        'Ein Fehler ist aufgetreten. Bitte versuche es erneut.'
      );
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
    }
  });
}

/**
 * Modern toast notifications
 */
export function showToast(
  type: 'success' | 'error' | 'info',
  message: string
): void {
  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.textContent = message;
  toast.setAttribute('role', 'alert');

  document.body.appendChild(toast);

  animate(
    toast,
    { opacity: [0, 1], y: [-20, 0] } as any,
    { duration: 0.3, easing: 'ease-out' } as any
  );

  setTimeout(() => {
    animate(toast, { opacity: 0, y: -20 }, { duration: 0.3 }).finished.then(
      () => toast.remove()
    );
  }, 5000);
}

/**
 * Lazy loading images with Intersection Observer
 */
export function useLazyImages(): void {
  const images = document.querySelectorAll<HTMLImageElement>('img[data-src]');

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target as HTMLImageElement;
          const src = img.dataset.src;

          if (src) {
            img.src = src;
            img.removeAttribute('data-src');
            animate(img, { opacity: [0, 1] }, { duration: 0.3 });
            observer.unobserve(img);
          }
        }
      });
    },
    { rootMargin: '50px' }
  );

  images.forEach((img) => observer.observe(img));
}

/**
 * Prefetch links on hover for better perceived performance
 */
export function usePrefetch(): void {
  const links = document.querySelectorAll<HTMLAnchorElement>(
    'a[href^="/"][data-prefetch]'
  );

  links.forEach((link) => {
    link.addEventListener('mouseenter', () => {
      const prefetchLink = document.createElement('link');
      prefetchLink.rel = 'prefetch';
      prefetchLink.href = link.href;
      document.head.appendChild(prefetchLink);
    });
  });
}
