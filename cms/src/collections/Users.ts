import type { CollectionConfig } from 'payload';
import { isAdmin, isAdminFieldLevel } from '@/access';

export const Users: CollectionConfig = {
  slug: 'users',
  auth: {
    useAPIKey: true,
  },
  access: {
    read: isAdmin,
    create: isAdmin,
    update: isAdmin,
    delete: isAdmin,
  },
  admin: {
    useAsTitle: 'name',
    defaultColumns: ['name', 'email', 'role'],
    group: 'System',
    description: 'Benutzer & Zugriffe',
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
      type: 'select',
      required: true,
      defaultValue: 'editor',
      label: 'Rolle',
      access: {
        update: isAdminFieldLevel,
      },
      options: [
        {
          label: 'Administrator',
          value: 'admin',
        },
        {
          label: 'Redakteur',
          value: 'editor',
        },
      ],
    },
  ],
};
