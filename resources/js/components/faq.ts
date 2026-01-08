export function initFAQ(): void {
  const faqItems = document.querySelectorAll<HTMLElement>('.faq-item');

  faqItems.forEach((item) => {
    const question = item.querySelector<HTMLButtonElement>(
      '.faq-item__question'
    );
    const answer = item.querySelector<HTMLElement>('.faq-item__answer');

    if (!question || !answer) return;

    const setAnswerHeight = (target: HTMLElement, expanded: boolean): void => {
      target.style.maxHeight =
        expanded || target.style.maxHeight === 'none'
          ? `${target.scrollHeight}px`
          : target.style.maxHeight;

      if (expanded) {
        const handleTransitionEnd = (event: TransitionEvent): void => {
          if (event.propertyName === 'max-height') {
            target.style.maxHeight = 'none';
            target.removeEventListener('transitionend', handleTransitionEnd);
          }
        };

        target.addEventListener('transitionend', handleTransitionEnd);
      } else {
        requestAnimationFrame(() => {
          target.style.maxHeight = '0px';
        });
      }
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
            otherAnswer.style.maxHeight = `${otherAnswer.scrollHeight}px`;
            requestAnimationFrame(() => {
              otherAnswer.style.maxHeight = '0px';
            });
          }
        }
      });

      // Toggle current item
      item.classList.toggle('active');
      question.setAttribute('aria-expanded', String(!isActive));

      setAnswerHeight(answer, !isActive);
    });
  });
}
