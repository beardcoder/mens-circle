import type { APIRoute } from 'astro';
import { getUpcomingEvents, getPastEvents } from '@/lib/payload';

const SITE_URL = import.meta.env.SITE_URL || 'https://maennerkreis-niederbayern.de';

export const GET: APIRoute = async () => {
  const upcoming = await getUpcomingEvents();
  const past = await getPastEvents();
  const allEvents = [...upcoming, ...past];

  const staticPages = [
    { url: '/', priority: '1.0', changefreq: 'weekly' },
    { url: '/events', priority: '0.9', changefreq: 'daily' },
    { url: '/testimonials', priority: '0.7', changefreq: 'weekly' },
    { url: '/impressum', priority: '0.3', changefreq: 'yearly' },
    { url: '/datenschutz', priority: '0.3', changefreq: 'yearly' },
  ];

  const eventPages = allEvents.map((event) => ({
    url: `/event/${event.slug}`,
    priority: '0.8',
    changefreq: 'weekly' as const,
    lastmod: event.createdAt,
  }));

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${staticPages
  .map(
    (page) => `  <url>
    <loc>${SITE_URL}${page.url}</loc>
    <changefreq>${page.changefreq}</changefreq>
    <priority>${page.priority}</priority>
  </url>`,
  )
  .join('\n')}
${eventPages
  .map(
    (page) => `  <url>
    <loc>${SITE_URL}${page.url}</loc>
    <changefreq>${page.changefreq}</changefreq>
    <priority>${page.priority}</priority>${page.lastmod ? `\n    <lastmod>${new Date(page.lastmod).toISOString().split('T')[0]}</lastmod>` : ''}
  </url>`,
  )
  .join('\n')}
</urlset>`;

  return new Response(xml, {
    headers: { 'Content-Type': 'application/xml' },
  });
};
