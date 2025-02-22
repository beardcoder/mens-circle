import clsx from 'clsx'

export const classes = {
  base: clsx(
    'text-white bg-gradient-to-r from-primary-500 via-primary-600 to-primary-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-primary-300 dark:focus:ring-primary-800 shadow-lg shadow-primary-500/50 dark:shadow-lg dark:shadow-primary-800/80 font-medium rounded-lg text-center inline-flex justify-center items-center transition-all duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed',
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
