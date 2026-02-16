/**
 * Global type definitions for the Männerkreis application
 */

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
  csrfToken: string;
}

declare global {
  interface Window {
    routes?: Routes;
  }
}
