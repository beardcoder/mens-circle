import type { CollectionConfig } from 'payload';
import { isAuthenticated, isAdmin, publicReadOnly } from '@/access';
import { autoSlug } from '@/hooks/slugify';

const timeFormatRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;

export const Events: CollectionConfig = {
  slug: 'events',
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
    defaultColumns: ['title', 'eventDate', 'location', 'published'],
    group: 'Veranstaltungen',
    description: 'Verwalte Events und Termine für den Männerkreis',
    listSearchableFields: ['title', 'location', 'city'],
    preview: (doc) => {
      if (doc.slug) {
        return `${process.env.SITE_URL || 'http://localhost:4321'}/events/${doc.slug}`;
      }
      return '';
    },
  },
  fields: [
    {
      type: 'tabs',
      tabs: [
        {
          label: 'Allgemein',
          fields: [
            {
              name: 'title',
              type: 'text',
              required: true,
              label: 'Titel',
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
              name: 'description',
              type: 'textarea',
              required: true,
              label: 'Beschreibung',
            },
            {
              name: 'image',
              type: 'upload',
              relationTo: 'media',
              label: 'Titelbild',
            },
          ],
        },
        {
          label: 'Termin & Ort',
          fields: [
            {
              name: 'eventDate',
              type: 'date',
              required: true,
              label: 'Datum',
              index: true,
              admin: {
                date: {
                  pickerAppearance: 'dayOnly',
                  displayFormat: 'dd.MM.yyyy',
                },
              },
            },
            {
              type: 'row',
              fields: [
                {
                  name: 'startTime',
                  type: 'text',
                  required: true,
                  label: 'Startzeit',
                  validate: (value: string | null | undefined) => {
                    if (!value) return 'Startzeit ist erforderlich';
                    if (!timeFormatRegex.test(value)) {
                      return 'Format: HH:MM (z.B. 19:00)';
                    }
                    return true;
                  },
                  admin: {
                    placeholder: '19:00',
                    width: '50%',
                    description: 'Format: HH:MM',
                  },
                },
                {
                  name: 'endTime',
                  type: 'text',
                  required: true,
                  label: 'Endzeit',
                  validate: (value: string | null | undefined) => {
                    if (!value) return 'Endzeit ist erforderlich';
                    if (!timeFormatRegex.test(value)) {
                      return 'Format: HH:MM (z.B. 21:30)';
                    }
                    return true;
                  },
                  admin: {
                    placeholder: '21:30',
                    width: '50%',
                    description: 'Format: HH:MM',
                  },
                },
              ],
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
              label: 'Straße & Hausnummer',
            },
            {
              type: 'row',
              fields: [
                {
                  name: 'zip',
                  type: 'text',
                  label: 'PLZ',
                  admin: {
                    width: '30%',
                  },
                },
                {
                  name: 'city',
                  type: 'text',
                  label: 'Stadt',
                  defaultValue: 'Straubing',
                  admin: {
                    width: '70%',
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
              name: 'maxParticipants',
              type: 'number',
              required: true,
              label: 'Max. Teilnehmer',
              defaultValue: 12,
              min: 1,
              admin: {
                description: 'Maximale Anzahl an Teilnehmern für dieses Event',
              },
            },
            {
              name: 'costBasis',
              type: 'text',
              label: 'Kostenbasis',
              defaultValue: 'Auf Spendenbasis',
            },
            {
              name: 'published',
              type: 'checkbox',
              defaultValue: false,
              label: 'Veröffentlicht',
              admin: {
                description: 'Event auf der Website sichtbar machen',
              },
            },
          ],
        },
      ],
    },
    {
      name: 'registrations',
      type: 'join',
      collection: 'registrations',
      on: 'event',
      admin: {
        description: 'Anmeldungen für dieses Event',
      },
    },
  ],
};
