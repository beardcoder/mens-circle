import type { CollectionConfig } from 'payload'

export const Registrations: CollectionConfig = {
  slug: 'registrations',
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
      defaultValue: 'registered',
      label: 'Status',
      options: [
        { label: 'Angemeldet', value: 'registered' },
        { label: 'Warteliste', value: 'waitlist' },
        { label: 'Abgesagt', value: 'cancelled' },
        { label: 'Teilgenommen', value: 'attended' },
      ],
    },
  ],
}
