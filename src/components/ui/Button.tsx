import clsx from 'clsx'
import type { FunctionComponent } from 'preact'
import { classes } from './ButtonProps'

export interface ButtonProps {
  href?: string
  type?: 'button' | 'submit' | 'reset'
  size?: 's' | 'm' | 'l'
  className?: string
  disabled?: boolean
  isLoading?: boolean
}

/**
 * Preact Button/Link Komponente mit 3 Größen: s, m, l.
 */
const Button: FunctionComponent<ButtonProps> = ({
  href,
  type = 'button',
  size = 'm',
  className = '',
  isLoading = false,
  children,
  ...props
}) => {
  const sizeClasses = classes.sizes[size] || classes.sizes.m
  const mergesClasses = clsx(classes.base, sizeClasses, className)

  // Falls href gesetzt ist, rendere einen Link, ansonsten einen Button
  if (href) {
    return (
      <a href={href} className={mergesClasses} {...props}>
        {isLoading ? 'Lädt...' : children || 'Jetzt teilnehmen'}
      </a>
    )
  }

  return (
    <button type={type} className={mergesClasses} {...props} disabled={isLoading}>
      {isLoading ? 'Lädt...' : children || 'Jetzt teilnehmen'}
    </button>
  )
}

export default Button
