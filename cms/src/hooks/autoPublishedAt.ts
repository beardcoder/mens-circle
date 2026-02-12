import type { CollectionBeforeChangeHook } from 'payload';

/**
 * Automatically sets publishedAt when the published field changes to true.
 * Clears publishedAt when unpublished.
 */
export const autoPublishedAt: CollectionBeforeChangeHook = ({
  data,
  originalDoc,
}) => {
  if (!data) return data;

  if (data.published && !originalDoc?.published) {
    data.publishedAt = new Date().toISOString();
  }

  if (data.published === false && originalDoc?.published) {
    data.publishedAt = null;
  }

  return data;
};
