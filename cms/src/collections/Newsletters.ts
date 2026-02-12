import type { CollectionConfig } from 'payload';

const isAuthenticated = ({ req: { user } }: { req: { user: unknown } }) => Boolean(user);

export const Newsletters: CollectionConfig = {
  slug: 'newsletters',
  access: {
    read: isAuthenticated,
    create: isAuthenticated,
    update: isAuthenticated,
    delete: isAuthenticated,
  },
  admin: {
    useAsTitle: 'subject',
    defaultColumns: ['subject', 'status', 'sentAt'],
  },
  fields: [
    {
      name: 'subject',
      type: 'text',
      required: true,
      label: 'Betreff',
    },
    {
      name: 'content',
      type: 'richText',
      required: true,
      label: 'Inhalt',
    },
    {
      name: 'status',
      type: 'select',
      required: true,
      defaultValue: 'draft',
      label: 'Status',
      options: [
        { label: 'Entwurf', value: 'draft' },
        { label: 'Wird gesendet', value: 'sending' },
        { label: 'Gesendet', value: 'sent' },
      ],
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'sentAt',
      type: 'date',
      label: 'Gesendet am',
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'recipientsCount',
      type: 'number',
      label: 'Empfänger',
      admin: {
        position: 'sidebar',
      },
    },
  ],
};
