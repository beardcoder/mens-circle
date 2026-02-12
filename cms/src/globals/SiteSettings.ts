import type { GlobalConfig } from 'payload';
import { isAdmin, publicReadOnly } from '@/access';

export const SiteSettings: GlobalConfig = {
  slug: 'site-settings',
  label: 'Einstellungen',
  access: {
    read: publicReadOnly,
    update: isAdmin,
  },
  admin: {
    group: 'System',
    description: 'Allgemeine Website-Einstellungen',
  },
  fields: [
    {
      type: 'tabs',
      tabs: [
        {
          label: 'Allgemein',
          fields: [
            {
              name: 'siteName',
              type: 'text',
              label: 'Seitenname',
              defaultValue: 'Männerkreis Niederbayern/ Straubing',
            },
            {
              name: 'siteDescription',
              type: 'textarea',
              label: 'Beschreibung',
              defaultValue: 'Ein Raum für echte Begegnung unter Männern.',
            },
            {
              name: 'homepage',
              type: 'relationship',
              relationTo: 'pages',
              label: 'Startseite',
              admin: {
                description: 'Die Seite, die als Startseite angezeigt wird',
              },
            },
          ],
        },
        {
          label: 'Kontakt',
          fields: [
            {
              name: 'contactEmail',
              type: 'email',
              label: 'Kontakt E-Mail',
            },
            {
              name: 'contactPhone',
              type: 'text',
              label: 'Kontakt Telefon',
            },
            {
              name: 'socialLinks',
              type: 'array',
              label: 'Social Links',
              admin: {
                description: 'Links zu sozialen Netzwerken und Kontaktmöglichkeiten',
              },
              fields: [
                {
                  type: 'row',
                  fields: [
                    {
                      name: 'platform',
                      type: 'select',
                      required: true,
                      admin: {
                        width: '33%',
                      },
                      options: [
                        { label: 'E-Mail', value: 'email' },
                        { label: 'Telefon', value: 'phone' },
                        { label: 'Instagram', value: 'instagram' },
                        { label: 'Facebook', value: 'facebook' },
                        { label: 'WhatsApp', value: 'whatsapp' },
                        { label: 'Website', value: 'website' },
                      ],
                    },
                    {
                      name: 'url',
                      type: 'text',
                      required: true,
                      label: 'URL / Wert',
                      admin: {
                        width: '33%',
                      },
                    },
                    {
                      name: 'label',
                      type: 'text',
                      label: 'Label',
                      admin: {
                        width: '33%',
                      },
                    },
                  ],
                },
              ],
            },
          ],
        },
        {
          label: 'Footer',
          fields: [
            {
              name: 'footerText',
              type: 'text',
              label: 'Footer-Text',
              defaultValue: '© 2025 Männerkreis Niederbayern',
            },
          ],
        },
      ],
    },
  ],
};
