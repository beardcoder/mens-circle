import clsx from 'clsx'
import type { FunctionComponent } from 'preact'
import type { HTMLAttributes } from 'preact/compat'
import { classes } from './ButtonProps'

export interface ButtonProps extends HTMLAttributes<HTMLElement> {
  /** Falls gesetzt, wird ein Link (<a>) gerendert, ansonsten ein Button (<button>) */
  href?: string
  /** Button-Typ (nur relevant, wenn kein href gesetzt ist) */
  type?: 'button' | 'submit' | 'reset'
  /** Größe: s, m oder l (Standard: m) */
  size?: 's' | 'm' | 'l'
  /** Zusätzliche CSS-Klassen */
  className?: string
}

/**
 * Preact Button/Link Komponente mit 3 Größen: s, m, l.
 */
const Button: FunctionComponent<ButtonProps> = ({
  href,
  type = 'button',
  size = 'm',
  className = '',
  children,
  ...props
}) => {
  const sizeClasses = classes.sizes[size] || classes.sizes.m
  const mergesClasses = clsx(classes.base, sizeClasses, className)

  // Falls href gesetzt ist, rendere einen Link, ansonsten einen Button
  if (href) {
    return (
      <a href={href} className={mergesClasses} {...props}>
        {children || 'Jetzt teilnehmen'}
      </a>
    )
  }

  return (
    <button type={type} className={mergesClasses} {...props}>
      {children || 'Jetzt teilnehmen'}
    </button>
  )
}

export default Button
