import type { CollectionBeforeChangeHook } from 'payload';

const umlautMap: Record<string, string> = {
  ä: 'ae',
  ö: 'oe',
  ü: 'ue',
  Ä: 'Ae',
  Ö: 'Oe',
  Ü: 'Ue',
  ß: 'ss',
};

function toSlug(text: string): string {
  return text
    .replace(/[äöüÄÖÜß]/g, (match) => umlautMap[match] || match)
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s_]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

/**
 * Auto-generates a slug from the title field on create,
 * or when the slug field is empty.
 */
export const autoSlug: CollectionBeforeChangeHook = ({ data, operation }) => {
  if (!data) return data;

  if (operation === 'create' && data.title && !data.slug) {
    data.slug = toSlug(data.title);
  }

  if (data.slug) {
    data.slug = toSlug(data.slug);
  }

  return data;
};
