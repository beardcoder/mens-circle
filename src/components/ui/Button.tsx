import type { FunctionComponent } from 'preact'
import type { HTMLAttributes } from 'preact/compat'

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
  // Basis-Klassen, basierend auf deiner Vorlage
  const baseClasses =
    'text-white bg-primary-500 hover:text-white hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium text-center me-2 mb-2 uppercase transition-colors duration-200 cursor-pointer'

  // Definition der größenabhängigen Klassen
  const sizeClassesMap: Record<string, string> = {
    s: 'text-xs px-4 py-2',
    m: 'text-sm px-5 py-2.5',
    l: 'text-lg px-6 py-3',
  }

  const sizeClasses = sizeClassesMap[size] || sizeClassesMap.m
  const classes = `${baseClasses} ${sizeClasses} ${className}`.trim()

  // Falls href gesetzt ist, rendere einen Link, ansonsten einen Button
  if (href) {
    return (
      <a href={href} className={classes} {...props}>
        {children || 'Jetzt teilnehmen'}
      </a>
    )
  }

  return (
    <button type={type} className={classes} {...props}>
      {children || 'Jetzt teilnehmen'}
    </button>
  )
}

export default Button
