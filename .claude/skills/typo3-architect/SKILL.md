---
name: typo3-architect
description: >-
  TYPO3 system architecture expert for enterprise TYPO3 implementations, site architecture design,
  and performance optimization. Activates when designing TYPO3 architecture, multi-site setups,
  performance strategies, content modeling, or security implementations; or when the user mentions
  TYPO3 architecture, multi-site, scalability, caching, or enterprise TYPO3 setup.
---

# TYPO3 Architect

## When to Apply

Activate this skill when:

- Designing TYPO3 site architecture and structure
- Planning multi-site or multi-domain TYPO3 setups
- Optimizing TYPO3 performance and caching strategies
- Creating content models and editorial workflows
- Implementing TYPO3 security architecture
- Planning version upgrades or migrations

## Core Expertise Areas

### Site Architecture Design

**Multi-site and multi-domain configurations:**
- Shared vs. separated TYPO3 installations
- Content sharing and inheritance strategies
- Domain and subdomain configurations
- Cross-site navigation and search

**Site structure:**
- Site tree structure and page hierarchy design
- Template and content inheritance patterns
- Site configuration (config/sites/*/config.yaml)

### Performance Optimization

**Caching strategies:**
- Page cache configuration and invalidation
- Content caching optimization
- CDN integration (Varnish, CloudFlare)
- Redis/Memcached integration

**Database optimization:**
- Query performance and indexing
- Connection pooling
- Database partitioning strategies

**Frontend optimization:**
- Asset bundling and minification
- Critical CSS and lazy loading
- Image optimization strategies

### Extension Architecture

- Custom extension design patterns
- Third-party extension evaluation
- Extension dependency management
- API design for extension interactions

### Content Strategy

- Content type modeling with TCA
- FlexForm vs. custom fields decisions
- Editorial workflow design
- Permission and access control patterns

## TYPO3 v14 Specifics

This project uses TYPO3 v14.1 with modern patterns:

**Site Sets (new in v14):**
- Site configuration in `Configuration/Sets/*/config.yaml`
- Dependency management with `optionalDependencies`
- Settings configuration structure

**Backend modules:**
- Register modules via `Configuration/Backend/Modules.php`
- Icon configuration in `Configuration/Icons.php`

**Content elements:**
- Core-native approach using only standard `tt_content` fields
- FlexForms for element-specific configuration
- No custom database columns

## Project Context (mens_circle)

**Extension location:** `packages/mens_circle`

**Key files:**
- Site config: `config/sites/mens-circle/config.yaml`
- Extension: `packages/mens_circle/ext_emconf.php`
- Site Set: `packages/mens_circle/Configuration/Sets/MensCircle/config.yaml`

**Current architecture:**
- Single site setup for men's circle events
- Event management with registrations
- Newsletter system
- Content blocks for landing pages

**Build commands:**
- Cache flush: `ddev exec vendor/bin/typo3 cache:flush`
- Frontend build: `bun run build` (uses Vite)
- Frontend dev: `bun run dev`

## Architecture Patterns

### Content Architecture

**Flexible content elements:**
- Use Core content elements where possible
- FlexForms for complex element configuration
- Avoid custom `tt_content` columns

**Content relationships:**
- Use Core relations (categories, tags)
- Extbase repository pattern for complex queries
- DataProcessors for frontend data preparation

### Security Architecture

**Access control:**
- Backend user groups and permissions
- Frontend access control via felogin
- API security for custom endpoints

**Security best practices:**
- Input validation and sanitization
- XSS prevention in Fluid templates
- CSRF protection for forms
- Secure password storage

### Performance Architecture

**Multi-level caching:**
1. Page cache (TYPO3 Core)
2. Content element caching
3. Database query caching
4. Asset caching (frontend)

**Database optimization:**
- Use Extbase repositories with proper query building
- Eager loading for relations
- Index optimization for custom tables

## Best Practices

### Architecture Design

- Plan for scalability and future growth
- Design flexible and maintainable structures
- Implement proper separation of concerns
- Follow TYPO3 Core conventions

### Performance Optimization

- Implement comprehensive caching strategies
- Optimize database queries and indexes
- Plan for traffic scaling
- Monitor and measure performance metrics

### Content Strategy

- Design intuitive content creation workflows
- Implement proper content governance
- Consider multi-channel publishing needs
- Use DataProcessors for frontend preparation

### Security Implementation

- Follow TYPO3 security guidelines
- Implement proper access controls
- Plan for security updates
- Regular security audits

## Common Tasks

### Multi-site Setup

When planning multi-site architectures:
1. Determine if sites share content or are separate
2. Plan domain/subdomain structure
3. Configure site settings in `config/sites/`
4. Consider shared vs. site-specific extensions
5. Plan permission structures

### Performance Tuning

When optimizing performance:
1. Enable and configure page caching
2. Implement Redis/Memcached if available
3. Optimize frontend asset loading
4. Review and optimize database queries
5. Configure CDN if applicable

### Content Modeling

When designing content structures:
1. Map content types to Core or custom elements
2. Design TCA/FlexForm structures
3. Plan content relationships
4. Design editorial workflows
5. Consider multi-language requirements

## Tools Used

- **View**: Analyze TYPO3 configuration files
- **Grep**: Find configuration patterns
- **Bash**: Execute TYPO3 console commands

## Related Skills

- **typo3-extension-dev**: For custom extension implementation
- **typo3-typoscript**: For TypoScript configuration
- **typo3-fluid**: For template architecture
- **typo3-content-blocks**: For modern content element design
