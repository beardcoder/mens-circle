# Hybrid CSS + Tailwind Architecture

This project uses a **hybrid approach** combining custom CSS with Tailwind utilities. This approach preserves the sophisticated design system while leveraging Tailwind's utility-first patterns where beneficial.

## Philosophy

**Custom CSS** excels at:
- Complex design tokens and theming
- Sophisticated animations and transitions
- Component-level interactions
- Decorative elements requiring pseudo-elements
- Features requiring @property, @keyframes, or complex selectors

**Tailwind CSS** excels at:
- Layout patterns (grid, flex, positioning)
- Spacing and sizing utilities
- Responsive design patterns
- Simple state variants (hover, focus, etc.)
- Rapid prototyping and iteration

## What Lives Where

### Custom CSS (`resources/css/`)

**Design Tokens** (`base/_variables.css`)
- OKLCH color palette with semantic tokens
- Fluid typography scale using clamp()
- Spacing scale with responsive values
- Motion system (easings, durations)
- Shadow and effect tokens
- All tokens available as CSS custom properties

**Complex Animations** (`base/_keyframes.css`)
- Breathing circle animations
- Custom keyframe sequences
- Ambient motion patterns
- Uses individual transform properties for composability

**Typography System** (`base/_typography.css`)
- Global element styles
- `.section-title`, `.eyebrow` patterns
- Font family and line height utilities

**Component Systems**
- Buttons with token-based variants (`components/_buttons.css`)
- Accordion with @property animations (`components/_accordion.css`)
- Modal, toast, forms with complex interactions
- Card components with hover state animations

**Section-Specific Features**
- Decorative pseudo-elements (breathing circles, noise textures)
- Complex background gradients with animations
- Section-specific typography overrides
- Custom layout patterns requiring grid/container queries

### Tailwind CSS (via `tailwind.config.js`)

**Extended with Design Tokens**
All CSS custom properties are available as Tailwind utilities:

```js
// Colors reference CSS variables
colors: {
  'earth-deep': 'var(--color-earth-deep)',
  'terracotta': 'var(--color-terracotta)',
  'bg-primary': 'var(--bg-primary)',
  // ... etc
}

// Spacing references CSS spacing scale
spacing: {
  'xs': 'var(--space-xs)',
  'md': 'var(--space-md)',
  'xl': 'var(--space-xl)',
  // ... etc
}
```

**Used in Templates**
Layout utilities applied directly in Blade templates:

```blade
<!-- Container -->
<div class="w-full max-w-container px-md mx-auto">

<!-- Grid layout -->
<div class="grid grid-cols-2 gap-xl items-center">

<!-- Responsive design -->
<div class="max-[800px]:grid-cols-1 max-[800px]:gap-lg">

<!-- Positioning -->
<section class="relative overflow-hidden py-xl">
```

## Migration Pattern

When migrating a section from pure CSS to hybrid:

1. **Keep** decorative features in CSS (pseudo-elements, complex animations)
2. **Move** structural layout to Tailwind classes in template
3. **Update** CSS file to document what was migrated
4. **Remove** only the layout CSS that's now in Tailwind

### Example: CTA Section

**Before** (Pure CSS):
```css
.cta-section {
  position: relative;
  overflow: hidden;
  text-align: center;
  background: var(--bg-tertiary);
}

.cta__content {
  position: relative;
  z-index: 1;
  max-inline-size: 700px;
  margin-inline: auto;
}
```

**After** (Hybrid):

CSS keeps only decorative breathing circles:
```css
/* Decorative Breathing Circles */
.cta-section::before,
.cta-section::after {
  /* Complex pseudo-element animations */
}
```

Template uses Tailwind for layout:
```blade
<section class="relative overflow-hidden py-xl text-center bg-bg-tertiary cta-section">
  <div class="w-full max-w-container px-md mx-auto">
    <div class="relative z-10 max-w-[700px] mx-auto">
```

## Benefits

1. **Design Token Single Source of Truth**: CSS variables in `_variables.css`, referenced by both CSS and Tailwind
2. **Preserved Sophistication**: Complex animations, OKLCH colors, fluid typography remain in CSS where they excel
3. **Improved DX**: Layout patterns use familiar Tailwind utilities
4. **Easier Maintenance**: Clear separation between decorative (CSS) and structural (Tailwind) concerns
5. **Performance**: Tailwind tree-shaking removes unused utilities
6. **Type Safety**: Tailwind IntelliSense in templates

## Guidelines

### When to Use Custom CSS

- Complex animations requiring @keyframes or @property
- Pseudo-elements (::before, ::after) for decorative purposes
- Component state that needs sophisticated transitions
- Selectors that would be verbose in Tailwind (nth-child, :has(), etc.)
- Features requiring cascade layers or @supports

### When to Use Tailwind

- Container and grid layouts
- Flexbox patterns
- Spacing (padding, margin, gap)
- Positioning (relative, absolute, z-index)
- Simple hover/focus states
- Responsive breakpoints
- Display utilities (flex, grid, block, hidden)

### When Either Works

- Text alignment → Use Tailwind (`text-center`)
- Background colors → Use Tailwind if semantic token exists
- Border radius → Use Tailwind (`rounded-md`)
- Simple transitions → Use Tailwind (`transition duration-normal`)

## Color Usage

The OKLCH color system lives in CSS (`_variables.css`). Both CSS and Tailwind reference these tokens:

```css
/* CSS */
background: var(--bg-primary);
color: var(--text-secondary);
```

```blade
<!-- Tailwind -->
<div class="bg-bg-primary text-text-secondary">
```

Both compile to the same CSS custom property, ensuring consistency across the system.

## Responsive Design

Use Tailwind's responsive prefixes for simple breakpoints:

```blade
<div class="grid grid-cols-2 max-[800px]:grid-cols-1">
```

Use custom CSS @media queries for complex responsive logic:

```css
@media (width <= 1000px) {
  .intro__layout {
    /* Complex responsive transformations */
  }
}
```

## Cascade Layers

Custom CSS maintains explicit cascade layers:

```css
@layer reset, tokens, base, components, sections, utilities;
```

Tailwind utilities are un-layered and win the cascade, allowing template-level overrides when needed.

## Future Considerations

- **Gradual Migration**: More sections can adopt Tailwind layout patterns over time
- **Component Extraction**: Complex components might move to reusable utilities
- **Design Token Evolution**: Tokens remain in CSS; Tailwind config stays as a bridge
- **Performance Monitoring**: Track CSS size as migration progresses

## Questions?

This hybrid approach is intentional and documented. When adding new features, consider:
1. Does it need complex animations or decorative elements? → Custom CSS
2. Is it primarily structural layout? → Tailwind utilities
3. Is it a design token? → CSS variable, referenced by both systems
