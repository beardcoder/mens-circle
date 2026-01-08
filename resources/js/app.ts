/**
 * Männerkreis Niederbayern / Straubing - Application Entry Point
 * Handles navigation, FAQ accordion, forms, animations, and calendar integration
 */

import './types';
import { initNavigation, initScrollHeader } from '@/components/navigation';
import { initFAQ } from '@/components/faq';
import { initForms } from '@/components/forms';
import {
  initScrollAnimations,
  initSmoothScroll,
} from '@/components/animations';
import { initCalendarIntegration } from '@/components/calendar';

/**
 * Initialize all application components when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initScrollHeader();
  initFAQ();
  initForms();
  initScrollAnimations();
  initCalendarIntegration();
  initSmoothScroll();
});
