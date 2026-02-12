import type { CollectionConfig } from 'payload';
import { isAuthenticated, isAdmin, publicReadOnly } from '@/access';
import { autoPublishedAt } from '@/hooks/autoPublishedAt';

export const Testimonials: CollectionConfig = {
  slug: 'testimonials',
  access: {
    read: publicReadOnly,
    create: () => true,
    update: isAuthenticated,
    delete: isAdmin,
  },
  defaultSort: 'sortOrder',
  hooks: {
    beforeChange: [autoPublishedAt],
  },
  admin: {
    useAsTitle: 'authorName',
    defaultColumns: ['authorName', 'published', 'sortOrder', 'createdAt'],
    group: 'Inhalte',
    description: 'Erfahrungsberichte moderieren',
    listSearchableFields: ['authorName', 'content'],
  },
  fields: [
    {
      name: 'content',
      type: 'textarea',
      required: true,
      label: 'Erfahrungsbericht',
      admin: {
        rows: 5,
      },
    },
    {
      type: 'row',
      fields: [
        {
          name: 'authorName',
          type: 'text',
          label: 'Name',
          admin: {
            width: '50%',
            description: 'Optional - kann auch anonym bleiben',
          },
        },
        {
          name: 'authorRole',
          type: 'text',
          label: 'Rolle / Beschreibung',
          admin: {
            width: '50%',
            placeholder: 'z.B. Teilnehmer seit 2024',
          },
        },
      ],
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
      type: 'collapsible',
      label: 'Veröffentlichung',
      fields: [
        {
          name: 'published',
          type: 'checkbox',
          defaultValue: false,
          label: 'Veröffentlicht',
        },
        {
          name: 'publishedAt',
          type: 'date',
          label: 'Veröffentlicht am',
          admin: {
            readOnly: true,
            description: 'Wird automatisch gesetzt bei Veröffentlichung',
            date: {
              displayFormat: 'dd.MM.yyyy',
            },
          },
        },
        {
          name: 'sortOrder',
          type: 'number',
          label: 'Reihenfolge',
          defaultValue: 0,
          admin: {
            description: 'Kleinere Zahlen erscheinen zuerst',
          },
        },
      ],
    },
  ],
};
