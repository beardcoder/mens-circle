import { createDirectus, readItem, readItems, rest, type DirectusFile } from '@directus/sdk'
import type { CustomDirectusTypes, DirectusFiles } from './types'

export const DIRECTUS_URL = 'https://api.mens-circle.de'

const directus = createDirectus<CustomDirectusTypes>(DIRECTUS_URL).with(rest())

export const getEvents = async () =>
  await directus.request(readItems('Event', { sort: ['start_date'], limit: 3, fields: ['*', { location: ['*'] }] }))
export type GetEvents = ReturnType<typeof getEvents>

export const getEvent = async (slug: string) => await directus.request(readItem('Event', slug))
export type GetEvent = ReturnType<typeof getEvent>

export const directusImage = (image: string | DirectusFile | DirectusFiles | Awaited<GetEvent>['image']) =>
  `${DIRECTUS_URL}/assets/${image instanceof Object ? image.id : image}`

export default directus
