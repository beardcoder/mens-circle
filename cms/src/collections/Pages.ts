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

export const Pages: CollectionConfig = {
  slug: 'pages',
  admin: {
    useAsTitle: 'title',
    defaultColumns: ['title', 'slug', 'published'],
  },
  fields: [
    {
      name: 'title',
      type: 'text',
      required: true,
      label: 'Titel',
    },
    {
      name: 'slug',
      type: 'text',
      required: true,
      unique: true,
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
    {
      name: 'meta',
      type: 'group',
      label: 'SEO',
      fields: [
        { name: 'metaTitle', type: 'text', label: 'Meta-Titel' },
        { name: 'metaDescription', type: 'textarea', label: 'Meta-Beschreibung' },
      ],
    },
    {
      name: 'published',
      type: 'checkbox',
      defaultValue: false,
      label: 'Veröffentlicht',
      admin: { position: 'sidebar' },
    },
  ],
};
