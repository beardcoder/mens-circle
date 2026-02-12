import type { CollectionConfig } from 'payload';
import { HeroBlock } from '@/blocks/Hero';
import { IntroBlock } from '@/blocks/Intro';
import { TextSectionBlock } from '@/blocks/TextSection';
import { ValueItemsBlock } from '@/blocks/ValueItems';
import { ModeratorBlock } from '@/blocks/Moderator';
import { JourneyStepsBlock } from '@/blocks/JourneySteps';
import { TestimonialsBlock } from '@/blocks/Testimonials';
import { FAQBlock } from '@/blocks/FAQ';
import { NewsletterBlock } from '@/blocks/Newsletter';
import { CTABlock } from '@/blocks/CTA';
import { WhatsAppCommunityBlock } from '@/blocks/WhatsAppCommunity';
import { isAuthenticated, isAdmin, publicReadOnly } from '@/access';
import { autoSlug } from '@/hooks/slugify';

export const Pages: CollectionConfig = {
  slug: 'pages',
  access: {
    read: publicReadOnly,
    create: isAuthenticated,
    update: isAuthenticated,
    delete: isAdmin,
  },
  hooks: {
    beforeChange: [autoSlug],
  },
  admin: {
    useAsTitle: 'title',
    defaultColumns: ['title', 'slug', 'published', 'updatedAt'],
    group: 'Inhalte',
    description: 'Seiten verwalten',
    listSearchableFields: ['title', 'slug'],
    preview: (doc) => {
      if (doc.slug) {
        return `${process.env.SITE_URL || 'http://localhost:4321'}/${doc.slug}`;
      }
      return '';
    },
  },
  fields: [
    {
      type: 'tabs',
      tabs: [
        {
          label: 'Inhalt',
          fields: [
            {
              name: 'title',
              type: 'text',
              required: true,
              label: 'Seitentitel',
              index: true,
            },
            {
              name: 'slug',
              type: 'text',
              required: true,
              unique: true,
              index: true,
              label: 'URL-Slug',
              admin: {
                description:
                  'Wird automatisch aus dem Titel generiert. Manuelle Anpassung möglich.',
              },
            },
            {
              name: 'content',
              type: 'blocks',
              label: 'Inhaltsblöcke',
              blocks: [
                HeroBlock,
                IntroBlock,
                TextSectionBlock,
                ValueItemsBlock,
                ModeratorBlock,
                JourneyStepsBlock,
                TestimonialsBlock,
                FAQBlock,
                NewsletterBlock,
                CTABlock,
                WhatsAppCommunityBlock,
              ],
            },
          ],
        },
        {
          label: 'SEO',
          fields: [
            {
              name: 'meta',
              type: 'group',
              fields: [
                {
                  name: 'metaTitle',
                  type: 'text',
                  label: 'Meta-Titel',
                  admin: {
                    description: 'Falls leer, wird der Seitentitel verwendet',
                  },
                },
                {
                  name: 'metaDescription',
                  type: 'textarea',
                  label: 'Meta-Beschreibung',
                  maxLength: 160,
                  admin: {
                    description: 'Empfohlen: 150-160 Zeichen',
                  },
                },
                {
                  name: 'ogImage',
                  type: 'upload',
                  relationTo: 'media',
                  label: 'Social Media Bild',
                  admin: {
                    description: 'Bild für Facebook, Twitter, etc.',
                  },
                },
              ],
            },
          ],
        },
        {
          label: 'Einstellungen',
          fields: [
            {
              name: 'published',
              type: 'checkbox',
              defaultValue: false,
              label: 'Veröffentlicht',
              admin: {
                description: 'Seite auf der Website sichtbar machen',
              },
            },
          ],
        },
      ],
    },
  ],
};
