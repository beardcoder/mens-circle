import type { CollectionConfig } from 'payload';

const isAuthenticated = ({ req: { user } }: { req: { user: unknown } }) => Boolean(user);

export const Testimonials: CollectionConfig = {
  slug: 'testimonials',
  access: {
    read: () => true,
    create: () => true,
    update: isAuthenticated,
    delete: isAuthenticated,
  },
  admin: {
    useAsTitle: 'authorName',
    defaultColumns: ['authorName', 'published', 'sortOrder', 'createdAt'],
  },
  fields: [
    {
      name: 'content',
      type: 'textarea',
      required: true,
      label: 'Erfahrungsbericht',
    },
    {
      name: 'authorName',
      type: 'text',
      label: 'Name',
    },
    {
      name: 'authorRole',
      type: 'text',
      label: 'Rolle / Beschreibung',
    },
    {
      name: 'email',
      type: 'email',
      required: true,
      label: 'E-Mail (nicht öffentlich)',
      admin: {
        description: 'Nur für Rückfragen, wird nicht öffentlich angezeigt.',
      },
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
    {
      name: 'publishedAt',
      type: 'date',
      label: 'Veröffentlicht am',
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'sortOrder',
      type: 'number',
      label: 'Reihenfolge',
      defaultValue: 0,
      admin: {
        position: 'sidebar',
      },
    },
  ],
};
