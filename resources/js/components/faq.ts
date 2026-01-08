export function initFAQ(): void {
  const faqItems = document.querySelectorAll<HTMLElement>('.faq-item');

  faqItems.forEach((item) => {
    const question = item.querySelector<HTMLButtonElement>(
      '.faq-item__question'
    );
    const answer = item.querySelector<HTMLElement>('.faq-item__answer');

    if (!question || !answer) return;

    const openAnswer = (target: HTMLElement): void => {
      target.style.maxHeight = `${target.scrollHeight}px`;

      const handleTransitionEnd = (event: TransitionEvent): void => {
        if (event.propertyName !== 'max-height') {
          return;
        }

        target.style.maxHeight = 'none';
        target.removeEventListener('transitionend', handleTransitionEnd);
      };

      target.addEventListener('transitionend', handleTransitionEnd);
    };

    const closeAnswer = (target: HTMLElement): void => {
      // Ensure we can animate from the current content height
      const currentHeight = target.scrollHeight;
      target.style.maxHeight = `${currentHeight}px`;

      requestAnimationFrame(() => {
        target.style.maxHeight = '0px';
      });
    };

    question.addEventListener('click', () => {
      const isActive = item.classList.contains('active');

      // Close all other items
      faqItems.forEach((otherItem) => {
        if (otherItem !== item) {
          otherItem.classList.remove('active');
          otherItem
            .querySelector('.faq-item__question')
            ?.setAttribute('aria-expanded', 'false');
          const otherAnswer =
            otherItem.querySelector<HTMLElement>('.faq-item__answer');

          if (otherAnswer) {
            closeAnswer(otherAnswer);
          }
        }
      });

      // Toggle current item
      item.classList.toggle('active');
      question.setAttribute('aria-expanded', String(!isActive));

      if (!isActive) {
        openAnswer(answer);
      } else {
        closeAnswer(answer);
      }
    });
  });
}
