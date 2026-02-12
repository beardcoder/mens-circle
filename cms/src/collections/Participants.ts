import type { CollectionConfig } from 'payload';

const isAuthenticated = ({ req: { user } }: { req: { user: unknown } }) => Boolean(user);

export const Participants: CollectionConfig = {
  slug: 'participants',
  access: {
    read: isAuthenticated,
    create: isAuthenticated,
    update: isAuthenticated,
    delete: isAuthenticated,
  },
  admin: {
    useAsTitle: 'email',
    defaultColumns: ['firstName', 'lastName', 'email'],
  },
  fields: [
    {
      name: 'firstName',
      type: 'text',
      required: true,
      label: 'Vorname',
    },
    {
      name: 'lastName',
      type: 'text',
      required: true,
      label: 'Nachname',
    },
    {
      name: 'email',
      type: 'email',
      required: true,
      unique: true,
      label: 'E-Mail',
    },
    {
      name: 'phone',
      type: 'text',
      label: 'Telefon',
    },
  ],
};
