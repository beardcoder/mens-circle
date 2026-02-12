import path from 'path';
import { fileURLToPath } from 'url';
import { buildConfig } from 'payload';
import { sqliteAdapter } from '@payloadcms/db-sqlite';
import { lexicalEditor } from '@payloadcms/richtext-lexical';
import { Users } from './collections/Users';
import { Events } from './collections/Events';
import { Participants } from './collections/Participants';
import { Registrations } from './collections/Registrations';
import { Newsletters } from './collections/Newsletters';
import { NewsletterSubscriptions } from './collections/NewsletterSubscriptions';
import { Testimonials } from './collections/Testimonials';
import { Pages } from './collections/Pages';
import { Media } from './collections/Media';
import { SiteSettings } from './globals/SiteSettings';

import { registerEndpoint } from './endpoints/register';
import { subscribeEndpoint } from './endpoints/subscribe';
import { unsubscribeEndpoint } from './endpoints/unsubscribe';
import { sendNewsletterEndpoint } from './endpoints/send-newsletter';
import { confirmSubscriptionEndpoint } from './endpoints/confirm-subscription';
import { submitTestimonialEndpoint } from './endpoints/submit-testimonial';

const filename = fileURLToPath(import.meta.url);
const dirname = path.dirname(filename);

export default buildConfig({
  admin: {
    user: Users.slug,
    meta: {
      titleSuffix: '– Männerkreis CMS',
    },
  },
  collections: [
    Users,
    Events,
    Participants,
    Registrations,
    Newsletters,
    NewsletterSubscriptions,
    Testimonials,
    Pages,
    Media,
  ],
  globals: [SiteSettings],
  editor: lexicalEditor(),
  db: sqliteAdapter({
    client: {
      url: process.env.DATABASE_URL || 'file:./data/cms.db',
    },
  }),
  plugins: [],
  endpoints: [
    {
      path: '/register',
      method: 'post',
      handler: registerEndpoint,
    },
    {
      path: '/subscribe',
      method: 'post',
      handler: subscribeEndpoint,
    },
    {
      path: '/unsubscribe/:token',
      method: 'get',
      handler: unsubscribeEndpoint,
    },
    {
      path: '/send-newsletter',
      method: 'post',
      handler: sendNewsletterEndpoint,
    },
    {
      path: '/confirm-subscription/:token',
      method: 'get',
      handler: confirmSubscriptionEndpoint,
    },
    {
      path: '/submit-testimonial',
      method: 'post',
      handler: submitTestimonialEndpoint,
    },
  ],
  cors: [process.env.SITE_URL || 'http://localhost:4321'],
  typescript: {
    outputFile: path.resolve(dirname, 'payload-types.ts'),
  },
  secret: process.env.PAYLOAD_SECRET || 'your-secret-key-change-in-production',
});
