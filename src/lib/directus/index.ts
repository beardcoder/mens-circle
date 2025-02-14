import { createDirectus, readItem, readItems, rest, type DirectusFile } from '@directus/sdk'
import type { CustomDirectusTypes, DirectusFiles } from './types'

export const DIRECTUS_URL = 'https://api.mens-circle.de'

const directus = createDirectus<CustomDirectusTypes>(DIRECTUS_URL).with(rest())

export const getEvents = async () => await directus.request(readItems('events'))
export type GetEvents = ReturnType<typeof getEvents>

export const getEvent = async (slug: string) => await directus.request(readItem('events', slug))
export type GetEvent = ReturnType<typeof getEvent>

export const directusImage = (image: string | DirectusFile | DirectusFiles | Awaited<GetEvent>['image']) =>
  `${DIRECTUS_URL}/assets/${image instanceof Object ? image.id : image}`

export default directus
