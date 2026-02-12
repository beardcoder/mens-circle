import type { CollectionConfig } from 'payload';

export const Testimonials: CollectionConfig = {
  slug: 'testimonials',
  admin: {
    useAsTitle: 'name',
    defaultColumns: ['name', 'published', 'createdAt'],
  },
  fields: [
    {
      name: 'name',
      type: 'text',
      required: true,
      label: 'Name',
    },
    {
      name: 'role',
      type: 'text',
      label: 'Rolle / Beschreibung',
    },
    {
      name: 'quote',
      type: 'textarea',
      required: true,
      label: 'Zitat',
    },
    {
      name: 'anonymous',
      type: 'checkbox',
      defaultValue: false,
      label: 'Anonym',
    },
    {
      name: 'published',
      type: 'checkbox',
      defaultValue: false,
      label: 'Veröffentlicht',
      admin: {
        position: 'sidebar',
      },
    },
  ],
};
