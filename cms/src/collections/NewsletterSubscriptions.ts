import type { CollectionConfig } from 'payload';
import crypto from 'crypto';

export const NewsletterSubscriptions: CollectionConfig = {
  slug: 'newsletter-subscriptions',
  admin: {
    defaultColumns: ['participant', 'status', 'createdAt'],
  },
  fields: [
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
      defaultValue: 'active',
      label: 'Status',
      options: [
        { label: 'Aktiv', value: 'active' },
        { label: 'Abgemeldet', value: 'unsubscribed' },
      ],
    },
    {
      name: 'token',
      type: 'text',
      required: true,
      unique: true,
      admin: {
        readOnly: true,
        position: 'sidebar',
      },
      hooks: {
        beforeValidate: [
          ({ value }) => {
            if (!value) {
              return crypto.randomBytes(32).toString('hex');
            }
            return value;
          },
        ],
      },
    },
  ],
};
