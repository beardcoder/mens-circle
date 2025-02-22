import clsx from 'clsx'

export const classes = {
  base: clsx(
    'text-white bg-primary-500 rounded-md hover:text-white hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium text-center me-2 mb-2 uppercase transition-colors duration-200 cursor-pointer absolut inline-flex items-center justify-center',
  ),

  sizes: {
    s: clsx('text-xs px-7 py-3'),
    m: clsx('text-sm px-8 py-3'),
    l: clsx('text-lg px-9 py-4'),
  },
}

export interface ButtonProps {
  href?: string
  type?: 'button' | 'submit' | 'reset'
  size?: 's' | 'm' | 'l'
  class?: string
  [key: string]: any
}
