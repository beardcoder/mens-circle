import type { CollectionConfig } from 'payload';

const isAuthenticated = ({ req: { user } }: { req: { user: unknown } }) => Boolean(user);

export const Registrations: CollectionConfig = {
  slug: 'registrations',
  access: {
    read: isAuthenticated,
    create: isAuthenticated,
    update: isAuthenticated,
    delete: isAuthenticated,
  },
  admin: {
    defaultColumns: ['event', 'participant', 'status', 'createdAt'],
  },
  fields: [
    {
      name: 'event',
      type: 'relationship',
      relationTo: 'events',
      required: true,
      label: 'Veranstaltung',
    },
    {
      name: 'participant',
      type: 'relationship',
      relationTo: 'participants',
      required: true,
      label: 'Teilnehmer',
    },
    {
      name: 'status',
      type: 'select',
      required: true,
      defaultValue: 'confirmed',
      label: 'Status',
      options: [
        { label: 'Bestätigt', value: 'confirmed' },
        { label: 'Storniert', value: 'cancelled' },
      ],
    },
    {
      name: 'consentTimestamp',
      type: 'date',
      label: 'Einwilligung erteilt am',
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'note',
      type: 'textarea',
      label: 'Anmerkung',
    },
  ],
};
