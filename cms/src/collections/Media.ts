import type { CollectionConfig } from 'payload';
import { isAuthenticated, publicReadOnly } from '@/access';

export const Media: CollectionConfig = {
  slug: 'media',
  access: {
    read: publicReadOnly,
    create: isAuthenticated,
    update: isAuthenticated,
    delete: isAuthenticated,
  },
  upload: {
    mimeTypes: ['image/*'],
    imageSizes: [
      {
        name: 'thumbnail',
        width: 400,
        height: 300,
        position: 'centre',
      },
      {
        name: 'card',
        width: 768,
        height: 512,
        position: 'centre',
      },
      {
        name: 'hero',
        width: 1920,
        height: 1080,
        position: 'centre',
      },
    ],
    adminThumbnail: 'thumbnail',
  },
  admin: {
    useAsTitle: 'alt',
    group: 'Inhalte',
    description: 'Bilder und Dateien',
  },
  fields: [
    {
      name: 'alt',
      type: 'text',
      required: true,
      label: 'Alt-Text',
      admin: {
        description: 'Beschreibung für Barrierefreiheit und SEO',
      },
    },
  ],
};
