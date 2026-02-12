import type { CollectionConfig } from 'payload';

const isAuthenticated = ({ req: { user } }: { req: { user: unknown } }) => Boolean(user);

export const Events: CollectionConfig = {
  slug: 'events',
  access: {
    read: () => true,
    create: isAuthenticated,
    update: isAuthenticated,
    delete: isAuthenticated,
  },
  admin: {
    useAsTitle: 'title',
    defaultColumns: ['title', 'eventDate', 'location', 'published'],
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
      admin: {
        position: 'sidebar',
      },
    },
    {
      name: 'description',
      type: 'textarea',
      required: true,
      label: 'Beschreibung',
    },
    {
      name: 'eventDate',
      type: 'date',
      required: true,
      label: 'Datum',
      admin: {
        date: {
          pickerAppearance: 'dayOnly',
          displayFormat: 'dd.MM.yyyy',
        },
      },
    },
    {
      name: 'startTime',
      type: 'text',
      required: true,
      label: 'Startzeit',
      admin: {
        placeholder: '19:00',
      },
    },
    {
      name: 'endTime',
      type: 'text',
      required: true,
      label: 'Endzeit',
      admin: {
        placeholder: '21:30',
      },
    },
    {
      name: 'location',
      type: 'text',
      required: true,
      label: 'Ortsname',
    },
    {
      name: 'street',
      type: 'text',
      label: 'Straße',
    },
    {
      name: 'zip',
      type: 'text',
      label: 'PLZ',
    },
    {
      name: 'city',
      type: 'text',
      label: 'Stadt',
      defaultValue: 'Straubing',
    },
    {
      name: 'maxParticipants',
      type: 'number',
      required: true,
      label: 'Max. Teilnehmer',
      defaultValue: 12,
      min: 1,
    },
    {
      name: 'costBasis',
      type: 'text',
      label: 'Kostenbasis',
      defaultValue: 'Auf Spendenbasis',
    },
    {
      name: 'image',
      type: 'upload',
      relationTo: 'media',
      label: 'Bild',
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
