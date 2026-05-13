/**
 * Global type definitions for the Männerkreis application
 */

import type { UmamiKit } from '@/utils/umami-kit';

export interface EventData {
  title: string;
  description: string;
  location: string;
  startDate: string;
  startTime: string;
  endDate: string;
  endTime: string;
}

export interface Routes {
  newsletter: string;
  eventRegister: string;
}

export interface ApiResponse {
  success: boolean;
  message: string;
}

declare global {
  interface Window {
    eventData?: EventData;
    routes: Routes;
    umamiTracker?: UmamiKit;
  }
}
