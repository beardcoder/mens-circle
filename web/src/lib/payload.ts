const CMS_URL = import.meta.env.CMS_URL || 'http://localhost:3001';
const API_KEY = import.meta.env.PAYLOAD_API_KEY || '';

export interface Event {
  id: string;
  title: string;
  slug: string;
  description: string;
  eventDate: string;
  startTime: string;
  endTime: string;
  location: string;
  city: string;
  maxParticipants: number;
  costBasis: string;
  image?: Media;
  published: boolean;
  createdAt: string;
}

export interface Media {
  id: string;
  url: string;
  alt: string;
  filename: string;
}

export interface Participant {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
}

export interface Registration {
  id: string;
  event: Event | string;
  participant: Participant | string;
  status: 'registered' | 'waitlist' | 'cancelled' | 'attended';
}

export interface Testimonial {
  id: string;
  name: string;
  role?: string;
  quote: string;
  anonymous: boolean;
  published: boolean;
}

export interface Page {
  id: string;
  title: string;
  slug: string;
  content: ContentBlock[];
  meta?: { metaTitle?: string; metaDescription?: string };
  published: boolean;
}

export interface SiteSettings {
  siteName: string;
  siteDescription: string;
  contactEmail: string;
  contactPhone: string;
  footerText: string;
  socialLinks: { platform: string; url: string; label?: string }[];
  homepage?: Page | string;
}

export type ContentBlock =
  | HeroBlock
  | IntroBlock
  | TextSectionBlock
  | ValueItemsBlock
  | ModeratorBlock
  | JourneyStepsBlock
  | TestimonialsBlock
  | FAQBlock
  | NewsletterBlock
  | CTABlock
  | WhatsAppCommunityBlock;

interface HeroBlock {
  blockType: 'hero';
  label?: string;
  title: string;
  description?: string;
  ctaText?: string;
  ctaLink?: string;
  backgroundImage?: Media;
}

interface IntroBlock {
  blockType: 'intro';
  eyebrow?: string;
  title: string;
  text: string;
  image?: Media;
  quote?: string;
}

interface TextSectionBlock {
  blockType: 'textSection';
  eyebrow?: string;
  title?: string;
  content: unknown;
}

interface ValueItemsBlock {
  blockType: 'valueItems';
  eyebrow?: string;
  title?: string;
  items: { number?: string; title: string; text: string }[];
}

interface ModeratorBlock {
  blockType: 'moderator';
  name: string;
  role?: string;
  bio: string;
  quote?: string;
  photo?: Media;
}

interface JourneyStepsBlock {
  blockType: 'journeySteps';
  eyebrow?: string;
  title?: string;
  steps: { number?: string; title: string; text: string }[];
}

interface TestimonialsBlock {
  blockType: 'testimonials';
  eyebrow?: string;
  title?: string;
}

interface FAQBlock {
  blockType: 'faq';
  eyebrow?: string;
  title?: string;
  items: { question: string; answer: string }[];
}

interface NewsletterBlock {
  blockType: 'newsletter';
  eyebrow?: string;
  title?: string;
  text?: string;
}

interface CTABlock {
  blockType: 'cta';
  eyebrow?: string;
  title: string;
  text?: string;
  buttonText?: string;
  buttonLink?: string;
}

interface WhatsAppCommunityBlock {
  blockType: 'whatsappCommunity';
  title?: string;
  text?: string;
  link?: string;
}

interface PayloadResponse<T> {
  docs: T[];
  totalDocs: number;
  totalPages: number;
}

async function fetchAPI<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };
  if (API_KEY) {
    headers['Authorization'] = `users API-Key ${API_KEY}`;
  }
  const res = await fetch(`${CMS_URL}/api${endpoint}`, {
    headers,
    ...options,
  });
  if (!res.ok) throw new Error(`API Error: ${res.status}`, await res.json());
  return res.json();
}

export async function getSettings(): Promise<SiteSettings> {
  return fetchAPI<SiteSettings>('/globals/site-settings');
}

export async function getPage(slug: string): Promise<Page | null> {
  const res = await fetchAPI<PayloadResponse<Page>>(
    `/pages?where[slug][equals]=${slug}&where[published][equals]=true&limit=1&depth=2`,
  );
  return res.docs[0] || null;
}

export async function getNextEvent(): Promise<Event | null> {
  const today = new Date().toISOString().split('T')[0];
  const res = await fetchAPI<PayloadResponse<Event>>(
    `/events?where[eventDate][greater_than_equal]=${today}&where[published][equals]=true&sort=eventDate&limit=1&depth=1`,
  );
  return res.docs[0] || null;
}

export async function getEvent(slug: string): Promise<Event | null> {
  const res = await fetchAPI<PayloadResponse<Event>>(
    `/events?where[slug][equals]=${slug}&where[published][equals]=true&limit=1&depth=1`,
  );
  return res.docs[0] || null;
}

export async function getEventRegistrationCount(eventId: string): Promise<number> {
  const res = await fetchAPI<PayloadResponse<Registration>>(
    `/registrations?where[event][equals]=${eventId}&where[status][in]=registered,attended&limit=0`,
  );
  return res.totalDocs;
}

export async function getTestimonials(): Promise<Testimonial[]> {
  const res = await fetchAPI<PayloadResponse<Testimonial>>(
    '/testimonials?where[published][equals]=true&limit=20&sort=-createdAt',
  );
  return res.docs;
}

export async function registerForEvent(data: {
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  eventId: string;
}) {
  const res = await fetch(`${CMS_URL}/api/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}

export async function subscribeNewsletter(data: { email: string }) {
  const res = await fetch(`${CMS_URL}/api/subscribe`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}
